<?php

namespace App\Jobs;

use App\Models\BulkJobItem;
use App\Models\MockupGenerationJob;
use App\Models\Mockup;
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

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public MockupGenerationJob $bulkJob,
        public BulkJobItem         $item,
        public Mockup              $mockup,
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
            $side        = $this->item->side;
            $mediaByRole = $mockup->getMedia('mockups')
                ->filter(function ($media) use ($side) {
                    return $media->getCustomProperty('side') === $side;
                })
                ->keyBy(function ($media) {
                    return $media->getCustomProperty('role');
                });

            $config = [
                'mockupConfig' => [
                    'scene'             => optional($mediaByRole->get('base'))->getFullUrl(),
                    'mask'              => optional($mediaByRole->get('mask'))->getFullUrl(),
                    'shadow'            => optional($mediaByRole->get('shadow'))->getFullUrl(),
                    'displacement'      => optional($mediaByRole->get('displacement'))->getFullUrl(),
                    'light'             => optional($mediaByRole->get('light'))->getFullUrl(),
                    'fillRatio'         => $mockup->fill_ratio / 100,
                    'displacementScale' => $mockup->displacement_scale,
                    'shadowStrength'    => $mockup->shadow_strength,
                    'lightStrength'     => $mockup->light_strength,
                    'vertices'          => $this->item->points,
                    'pixiBundleUrl'     => config('services.node_render_url') . '/pixi-render-bundle.js',
                ],
                'designUrl' => $this->item->getDesignUrl(),
                'color'     => $this->item->color,
                'side'      => $this->item->side,
            ];

            Log::error("configFront", $config);

            $response = Http::timeout(30)->post(
                config('services.node_render_url') . '/api/render',
                $config
            );

            if (!$response->successful()) {
                throw new \Exception("Render service returned: " . $response->body());
            }

            $hex      = strtolower(ltrim(trim($this->item->color), '#'));
            $template = $this->item->template;
            $tempPath = sys_get_temp_dir() . "/mockup_{$this->mockup->id}_{$template->id}_{$side}_{$hex}.png";
            file_put_contents($tempPath, $response->body());

            $protectedHexes = $this->mockup->templates()
                ->wherePivotNotNull('model_color')
                ->get()
                ->pluck('pivot.model_color')
                ->map(fn($c) => strtolower(ltrim(trim($c), '#')))
                ->filter()
                ->unique()
                ->values()
                ->all();

            // -----------------------------------------------------------------------
            // Check pivot model_color for THIS template to determine model_image flag.
            // -----------------------------------------------------------------------
            $pivotModelColor = $this->mockup->templates()
                ->wherePivot('template_id', $template->id)
                ->first()
                ?->pivot
                ?->model_color;

            $pivotModelColorHex = $pivotModelColor
                ? strtolower(ltrim(trim($pivotModelColor), '#'))
                : null;

            $isModelImage = $pivotModelColorHex && $pivotModelColorHex === $hex ? 1 : 0;

            // -----------------------------------------------------------------------
            // Delete old media for this template + side + hex before saving new one
            // -----------------------------------------------------------------------
            $deleteQuery = $this->mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $template->id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.category_id')) = ?", [(int) $this->mockup->category_id]);

            if (!empty($protectedHexes)) {
                foreach ($protectedHexes as $protectedHex) {
                    $deleteQuery->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) != ?",
                        [$protectedHex]
                    );
                }
            }

            $deleteQuery->get()->each(fn($media) => $media->delete());

            try {
                $this->mockup
                    ->addMedia($tempPath)
                    ->usingFileName("mockup_{$side}_tpl{$template->id}_{$hex}.png")
                    ->withCustomProperties([
                        'side'        => $side,
                        'template_id' => (string) $template->id,
                        'hex'         => $hex,
                        'category_id' => (int) $this->mockup->category_id,
                        'product_ids' => (array) $mockup->products->pluck('id')->toArray(),
                        'model_image' => $isModelImage,
                    ])
                    ->toMediaCollection('generated_mockups');

            } finally {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }

            $media = $this->mockup->media()
                ->where('collection_name', 'generated_mockups')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $template->id])
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
                ->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex'))) = ?", [$hex])
                ->latest()
                ->first();

            $this->item->update([
                'status'      => 'completed',
                'output_path' => $media ? parse_url($media->getUrl(), PHP_URL_PATH) : null,
            ]);

            $this->checkCompletion();

        } catch (Throwable $e) {
            $this->item->update(['error_message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        $this->item->update([
            'status'        => 'failed',
            'error_message' => $e->getMessage(),
        ]);

        $this->bulkJob->increment('failed_count');
        $this->checkCompletion();
    }

    private function checkCompletion(): void
    {
        DB::transaction(function () {
            $job = MockupGenerationJob::lockForUpdate()->find($this->bulkJob->id);

            if (!$job || in_array($job->status, ['completed', 'completed_with_errors', 'failed', 'cancelled'])) {
                return;
            }

            // -----------------------------------------------------------------------
            // Group all items by template_id + color + side, then sum via foreach.
            //
            // Example breakdown entry:
            // { "template_id": "abc-123", "color": "#FF0000", "side": "front", "total": 1, "completed": 1, "failed": 0 }
            // { "template_id": "abc-123", "color": "#FF0000", "side": "back",  "total": 1, "completed": 0, "failed": 1 }
            // -----------------------------------------------------------------------
            $rows = BulkJobItem::where('bulk_job_id', $job->id)
                ->selectRaw("
                    template_id,
                    color,
                    side,
                    COUNT(*) as total,
                    SUM(status = 'completed') as completed,
                    SUM(status = 'failed') as failed,
                    SUM(status IN ('pending', 'processing')) as pending
                ")
                ->groupBy('template_id', 'color', 'side')
                ->orderBy('template_id')
                ->orderBy('color')
                ->orderBy('side')
                ->get();

            $completed = 0;
            $failed    = 0;
            $total     = 0;
            $pending   = 0;
            $breakdown = [];

            foreach ($rows as $row) {
                $completed += (int) $row->completed;
                $failed    += (int) $row->failed;
                $total     += (int) $row->total;
                $pending   += (int) $row->pending;

                $breakdown[] = [
                    'template_id' => $row->template_id,
                    'color'       => $row->color,
                    'side'        => $row->side,
                    'total'       => (int) $row->total,
                    'completed'   => (int) $row->completed,
                    'failed'      => (int) $row->failed,
                ];
            }

            // Don't mark complete until ALL items are done
            if ($pending > 0) {
                return;
            }

            $job->update([
                'completed_count'             => $completed,
                'failed_count'                => $failed,
                'completed_by_template_color' => $breakdown,
                'status'                      => match (true) {
                    $failed === 0    => 'completed',
                    $completed === 0 => 'failed',
                    default          => 'completed_with_errors',
                },
                'completed_at'                => now(),
            ]);
        });
    }
}
