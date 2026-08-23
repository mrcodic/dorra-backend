<?php

namespace App\Jobs;

use App\Models\BulkJobItem;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RenderMockupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public MockupGenerationJob $bulkJob,
        public BulkJobItem $item,
        public Mockup $mockup,
    ) {}

    public function handle(): void
    {
        if ($this->bulkJob->fresh()->status === 'cancelled') {
            $this->item->update(['status' => 'cancelled']);
            return;
        }

        $this->item->update(['status' => 'processing']);

        $mockup = $this->mockup;

        try {
            $side = $this->item->side;

            $mediaByRole = $mockup->getMedia('mockups')
                ->filter(fn ($media) => $media->getCustomProperty('side') === $side)
                ->keyBy(fn ($media) => $media->getCustomProperty('role'));

            Log::info('render_debug', [
                'side' => $side,
                'available_sides' => $mockup->getMedia('mockups')->pluck('custom_properties.side')->unique()->values(),
                'available_roles_for_side' => $mediaByRole->keys(),
            ]);

            $config = [
                'mockupConfig' => [
                    'scene' => optional($mediaByRole->get('base'))->getFullUrl(),
                    'mask' => optional($mediaByRole->get('mask'))->getFullUrl(),
                    'shadow' => optional($mediaByRole->get('shadow'))->getFullUrl(),
                    'displacement' => optional($mediaByRole->get('displacement'))->getFullUrl(),
                    'light' => optional($mediaByRole->get('light'))->getFullUrl(),
                    'fillRatio' => $mockup->fill_ratio / 100,
                    'displacementScale' => $mockup->displacement_scale,
                    'shadowStrength' => $mockup->shadow_strength,
                    'lightStrength' => $mockup->light_strength,
                    'vertices' => $this->item->points,
                    'pixiBundleUrl' => config('services.node_render_url') . '/pixi-render-bundle.js',
                ],
                'designUrl' => $this->item->getDesignUrl(),
                'color' => $this->item->color,
                'side' => $this->item->side,
            ];

            Log::info('render_config', $config);

            $hex = strtolower(ltrim(trim($this->item->color), '#'));
            $template = $this->item->template;

            $tempPath = sys_get_temp_dir()
                . "/mockup_{$mockup->id}_{$template->id}_{$side}_{$hex}.png";

            $response = Http::withOptions([
                'sink' => $tempPath,
            ])
                ->timeout(30)
                ->post(config('services.node_render_url') . '/api/render', $config);

            if (!$response->successful()) {
                $error = file_exists($tempPath) ? file_get_contents($tempPath) : '';

                throw new \Exception(
                    'Render service returned: ' . $error
                );
            }

            /*
             * ---------------------------------------------------------------
             * FIRST PIVOT COLOR = PRIMARY / MODEL COLOR
             * ---------------------------------------------------------------
             */
            $currentTemplate = $mockup->templates()
                ->where('templates.id', $template->id)
                ->first();

            $pivotColors = $currentTemplate?->pivot?->colors ?? [];

            if (is_string($pivotColors)) {
                $pivotColors = json_decode($pivotColors, true) ?? [];
            }

            if (!is_array($pivotColors)) {
                $pivotColors = [];
            }

            $primaryColor = collect($pivotColors)
                ->filter(fn ($color) => is_string($color) && trim($color) !== '')
                ->first();

            $primaryHex = $primaryColor
                ? strtolower(ltrim(trim($primaryColor), '#'))
                : null;

            $isModelImage = $primaryHex !== null && $primaryHex === $hex ? 1 : 0;

            /*
             * Keep pivot model_color always synced with first color.
             * No colors => model_color = null.
             */
            $currentModelColor = $currentTemplate?->pivot?->model_color;

            $currentModelHex = $currentModelColor
                ? strtolower(ltrim(trim($currentModelColor), '#'))
                : null;

            if ($currentModelHex !== $primaryHex) {
                $mockup->templates()->updateExistingPivot($template->id, [
                    'model_color' => $primaryColor,
                ]);
            }

            /*
             * If this render is the primary color, clear model_image from
             * other generated images for this template + side first.
             */
            if ($isModelImage) {
                $mockup->media()
                    ->where('collection_name', 'generated_mockups')
                    ->whereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                        [(string) $template->id]
                    )
                    ->whereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?",
                        [$side]
                    )
                    ->get()
                    ->each(function ($media) {
                        if ((int) $media->getCustomProperty('model_image', 0) !== 1) {
                            return;
                        }

                        $media->setCustomProperty('model_image', 0);
                        $media->save();
                    });
            }

            /*
             * Delete old generated image for same:
             * template + side + color
             */
            $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                    [(string) $template->id]
                )
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?",
                    [$side]
                )
                ->whereRaw(
                    "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                    [$hex]
                )
                ->get()
                ->each(fn ($media) => $media->delete());

            try {
                $mockup
                    ->addMedia($tempPath)
                    ->usingFileName("mockup_{$side}_tpl{$template->id}_{$hex}.png")
                    ->withCustomProperties([
                        'side' => $side,
                        'template_id' => (string) $template->id,
                        'hex' => $hex,
                        'category_id' => (int) $mockup->category_id,
                        'product_ids' => $mockup->products->pluck('id')->toArray(),
                        'model_image' => $isModelImage,
                    ])
                    ->toMediaCollection('generated_mockups');
            } finally {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }

            $media = $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                    [(string) $template->id]
                )
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?",
                    [$side]
                )
                ->whereRaw(
                    "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                    [$hex]
                )
                ->latest()
                ->first();

            $this->item->update([
                'status' => 'completed',
                'output_path' => $media
                    ? parse_url($media->getUrl(), PHP_URL_PATH)
                    : null,
            ]);

            MockupGenerationJob::where('id', $this->bulkJob->id)
                ->increment('completed_count');

            $this->checkCompletion();
        } catch (Throwable $e) {
            $this->item->update([
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $this->item->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
        ]);

        MockupGenerationJob::where('id', $this->bulkJob->id)
            ->increment('failed_count');

        $this->checkCompletion();
    }

    private function checkCompletion(): void
    {
        DB::transaction(function () {
            $job = MockupGenerationJob::lockForUpdate()
                ->find($this->bulkJob->id);

            if (
                !$job ||
                in_array(
                    $job->status,
                    ['completed', 'completed_with_errors', 'failed', 'cancelled'],
                    true
                )
            ) {
                return;
            }

            $pending = BulkJobItem::where('bulk_job_id', $job->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            if ($pending > 0) {
                return;
            }

            $completed = (int) $job->completed_count;
            $failed = (int) $job->failed_count;

            $job->update([
                'status' => match (true) {
                    $failed === 0 => 'completed',
                    $completed === 0 => 'failed',
                    default => 'completed_with_errors',
                },
                'completed_at' => now(),
            ]);
        });
    }
}
