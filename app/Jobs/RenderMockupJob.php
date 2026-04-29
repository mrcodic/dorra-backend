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

            $side       = $this->item->side;
            $mediaByRole = $mockup->getMedia('mockups')
                ->filter(function ($media) use ($side) {
                    return $media->getCustomProperty('side') === $side;
                })
                ->keyBy(function ($media) {
                    return $media->getCustomProperty('role');
                });
            $config = [
                'mockupConfig' => [
                    'scene' => $mediaByRole->get('base'),
                    'mask' => $mediaByRole->get('mask'),
                    'shadow' => $mediaByRole->get('shadow'),
                    'displacement' => $mediaByRole->get('displacement'),
                    'light' => $mediaByRole->get('light'),
                    'fillRatio'        => $mockup->fill_ratio / 100,
                    'displacementScale' => $mockup->displacement_scale,
                    'shadowStrength'   => $mockup->shadow_strength,
                    'lightStrength'    => $mockup->light_strength,
                    'vertices'         => $this->item->points,
                    'pixiBundleUrl'    => config('services.editor_url').'/pixi-render-bundle.js',
                ],
                'designUrl' =>'https://dev.dorraprint.com//storage/22455/preview_69ef3b50124a7.png',
                'color'    => $this->item->color,
                'side'     => $this->item->side,
            ];
            Log::error("configFront", $config);

            $response = Http::timeout(30)->post(
                config('services.node_render_url') . '/api/render',
                $config
            );

            if (!$response->successful()) {
                throw new \Exception("Render service returned: " . $response->status());
            }


            $hex       = strtolower(ltrim(trim($this->item->color), '#'));
            $template= $this->item->template;
            $tempPath  = sys_get_temp_dir() . "/mockup_{$this->mockup->id}_{$template->id}_{$side}_{$hex}.png";
            file_put_contents($tempPath, $response->body());

            try {
                $this->mockup
                    ->addMedia($tempPath)
                    ->usingFileName("mockup_{$side}_tpl{$template->id}_{$hex}.png")
                    ->withCustomProperties([
                        'side'        => $side,
                        'template_id' => (string) $template->id,
                        'hex'         => $hex,
                        'category_id' => (int) $this->mockup->category_id,
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
                'output_path' => $media?->getUrl(),
            ]);

        } catch (Throwable $e) {
            $this->item->update([
                'status'        => 'failed',
                'error_message' => $config,
            ]);

            $this->bulkJob->increment('failed_count');
            $this->checkCompletion();

            throw $e;
        }
    }


    private function checkCompletion(): void
    {
        $job = $this->bulkJob->fresh();

        if (($job->completed_count + $job->failed_count) >= $job->total_count) {
            $job->update([
                'status'       => $job->failed_count > 0 && $job->completed_count === 0
                    ? 'failed'
                    : ($job->failed_count > 0 ? 'completed_with_errors' : 'completed'),
                'completed_at' => now(),
            ]);
        }
    }


}
