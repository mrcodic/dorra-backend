<?php

namespace App\Jobs;


use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;


class RenderFabricJsonToPngJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct
    (
        public string   $fabricJson,
        public HasMedia $model,
        public string   $collectionName = 'default'
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jsonPath = storage_path('app/fabric-json.json');
        $tempPngPath = storage_path('app/fabric-rendered.png');
        $nodeScriptPath = base_path('fabric-renderer/renderFabric.js');

        try {
            file_put_contents($jsonPath, $this->fabricJson);

            $cmd = "node {$nodeScriptPath} {$jsonPath} {$tempPngPath} 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Fabric render job failed', ['cmd' => $cmd, 'output' => implode("\n", $output)]);
                throw new \Exception("Failed to render PNG from Fabric JSON");
            }

            if (!file_exists($tempPngPath)) {
                Log::error('Rendered PNG file missing after node script', ['path' => $tempPngPath]);
                throw new \Exception("Rendered PNG file not found");
            }

            if ($this->model->hasMedia($this->collectionName)) {
                $this->model->clearMediaCollection($this->collectionName);
            }
          $firstMedia =  $this->model->addMedia($tempPngPath)
                ->usingFileName('fabric_rendered_' . uniqid() . '.png')
                ->toMediaCollection($this->collectionName);

            if ($this->model instanceof Design) {
                $designVersion =  $this->model->versions()->first();
                $firstMedia->copy($designVersion, 'design-versions');
            }

        } finally {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            if (file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }
        }

    }

}
