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
    public array $backoff = [10, 30, 60];

    public function __construct(
        public MockupGenerationJob $bulkJob,
        public BulkJobItem $item,
        public Mockup $mockup,
    ) {}

    public function handle(): void
    {
        $bulkJob = $this->bulkJob->fresh();

        if (!$bulkJob || $bulkJob->status === 'cancelled') {
            $this->item->update(['status' => 'cancelled']);
            return;
        }

        $this->item->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        $mockup = $this->mockup->fresh();

        try {
            $side = (string) $this->item->side;
            $template = $this->item->template;

            if (!$template) {
                throw new \RuntimeException('Template not found for render item.');
            }

            $rawColor = $this->item->color;
            $hasColor = is_string($rawColor) && trim($rawColor) !== '';
            $hex = $hasColor ? strtolower(ltrim(trim($rawColor), '#')) : null;
            $fileHex = $hex ?? 'base';

            $mediaByRole = $mockup->getMedia('mockups')
                ->filter(fn ($media) => $media->getCustomProperty('side') === $side)
                ->keyBy(fn ($media) => $media->getCustomProperty('role'));

            $designUrl = $this->item->getDesignUrl();

            if (!$designUrl) {
                throw new \RuntimeException("Design URL not found for template {$template->id} side {$side}.");
            }

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
                    'pixiBundleUrl' => rtrim(config('services.node_render_url'), '/') . '/pixi-render-bundle.js',
                ],
                'designUrl' => $designUrl,
                'color' => $hasColor ? $rawColor : null,
                'side' => $side,
            ];

            Log::info('render_config', [
                'bulk_job_id' => $this->bulkJob->id,
                'item_id' => $this->item->id,
                'mockup_id' => $mockup->id,
                'template_id' => $template->id,
                'side' => $side,
                'color' => $rawColor,
                'config' => $config,
            ]);

            $tempPath = sys_get_temp_dir()
                . "/mockup_{$mockup->id}_{$template->id}_{$side}_{$fileHex}_{$this->item->id}.png";

            $response = Http::withOptions(['sink' => $tempPath])
                ->connectTimeout(10)
                ->timeout(90)
                ->post(rtrim(config('services.node_render_url'), '/') . '/api/render', $config);

            if (!$response->successful()) {
                $error = file_exists($tempPath) ? file_get_contents($tempPath) : '';
                throw new \RuntimeException(
                    'Render service returned HTTP ' . $response->status()
                    . ($error !== '' ? ': ' . mb_substr($error, 0, 2000) : '')
                );
            }

            if (!file_exists($tempPath) || filesize($tempPath) === 0) {
                throw new \RuntimeException('Render service returned an empty image.');
            }

            $currentTemplate = $mockup->templates()
                ->where('templates.id', $template->id)
                ->first();

            if (!$currentTemplate) {
                throw new \RuntimeException("Template {$template->id} is no longer attached to mockup {$mockup->id}.");
            }

            $pivotColors = $currentTemplate->pivot->colors ?? [];

            if (is_string($pivotColors)) {
                $pivotColors = json_decode($pivotColors, true) ?? [];
            }

            if (!is_array($pivotColors)) {
                $pivotColors = [];
            }

            $pivotColors = collect($pivotColors)
                ->filter(fn ($color) => is_string($color) && trim($color) !== '')
                ->values()
                ->all();

            $primaryColor = $pivotColors[0] ?? null;
            $primaryHex = $primaryColor
                ? strtolower(ltrim(trim($primaryColor), '#'))
                : null;

            $currentModelColor = $currentTemplate->pivot->model_color;
            $currentModelHex = $currentModelColor
                ? strtolower(ltrim(trim((string) $currentModelColor), '#'))
                : null;

            if ($hasColor) {
                if (!$currentModelHex || !in_array(
                        $currentModelHex,
                        collect($pivotColors)
                            ->map(fn ($color) => strtolower(ltrim(trim((string) $color), '#')))
                            ->all(),
                        true
                    )) {
                    $currentModelColor = $primaryColor;
                    $currentModelHex = $primaryHex;

                    $mockup->templates()->updateExistingPivot($template->id, [
                        'model_color' => $primaryColor,
                    ]);
                }

                $isModelImage = $currentModelHex !== null && $currentModelHex === $hex ? 1 : 0;
            } else {
                if (!empty($pivotColors)) {
                    throw new \RuntimeException(
                        "Null-color render item cannot be used while template {$template->id} still has colors."
                    );
                }

                if ($currentModelColor !== null) {
                    $mockup->templates()->updateExistingPivot($template->id, [
                        'model_color' => null,
                    ]);
                }

                $isModelImage = 1;
            }

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
                        if ((int) $media->getCustomProperty('model_image', 0) === 1) {
                            $media->setCustomProperty('model_image', 0);
                            $media->save();
                        }
                    });
            }

            $deleteQuery = $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                    [(string) $template->id]
                )
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?",
                    [$side]
                );

            if ($hasColor) {
                $deleteQuery->whereRaw(
                    "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                    [$hex]
                );
            } else {
                $deleteQuery->where(function ($query) {
                    $query->whereNull('custom_properties->hex')
                        ->orWhereRaw(
                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '') = ''"
                        );
                });
            }

            $deleteQuery->get()->each(fn ($media) => $media->delete());

            try {
                $mockup
                    ->addMedia($tempPath)
                    ->usingFileName("mockup_{$side}_tpl{$template->id}_{$fileHex}.png")
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
                    @unlink($tempPath);
                }
            }

            $mediaQuery = $mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                    [(string) $template->id]
                )
                ->whereRaw(
                    "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?",
                    [$side]
                );

            if ($hasColor) {
                $mediaQuery->whereRaw(
                    "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                    [$hex]
                );
            } else {
                $mediaQuery->where(function ($query) {
                    $query->whereNull('custom_properties->hex')
                        ->orWhereRaw(
                            "COALESCE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '') = ''"
                        );
                });
            }

            $media = $mediaQuery->latest()->first();

            $this->item->update([
                'status' => 'completed',
                'error_message' => null,
                'output_path' => $media
                    ? parse_url($media->getUrl(), PHP_URL_PATH)
                    : null,
            ]);

            MockupGenerationJob::where('id', $this->bulkJob->id)
                ->increment('completed_count');

            $this->checkCompletion();
        } catch (Throwable $e) {
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            $this->item->update([
                'error_message' => $e->getMessage(),
            ]);

            Log::error('RenderMockupJob failed attempt', [
                'bulk_job_id' => $this->bulkJob->id,
                'item_id' => $this->item->id,
                'mockup_id' => $this->mockup->id,
                'template_id' => $this->item->template_id,
                'side' => $this->item->side,
                'color' => $this->item->color,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $item = $this->item->fresh();
        $existingError = $item?->error_message;

        $this->item->update([
            'status' => 'failed',
            'error_message' => $existingError ?: $e->getMessage(),
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

            $completed = BulkJobItem::where('bulk_job_id', $job->id)
                ->where('status', 'completed')
                ->count();

            $failed = BulkJobItem::where('bulk_job_id', $job->id)
                ->where('status', 'failed')
                ->count();

            $job->update([
                'completed_count' => $completed,
                'failed_count' => $failed,
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
