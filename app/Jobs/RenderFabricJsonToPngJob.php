<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Storage;

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

        file_put_contents($jsonPath, $this->fabricJson);

        $cmd = "node {$nodeScriptPath} {$jsonPath} {$tempPngPath}";
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('Fabric render job failed', ['cmd' => $cmd, 'output' => $output]);
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            throw new \Exception("Failed to render PNG from Fabric JSON");
        }

        if (file_exists($tempPngPath)) {
            if ($this->model->hasMedia('models')) {
                $this->model->clearMediaCollection($this->collectionName);
            }
            $this->model->addMedia($tempPngPath)
                ->usingFileName('fabric_rendered_' . uniqid() . '.png')
                ->toMediaCollection($this->collectionName);
        } else {
            Log::error('Rendered PNG file missing after node script', ['path' => $tempPngPath]);
            throw new \Exception("Rendered PNG file not found");
        }

        // Cleanup temp files
        if (file_exists($jsonPath)) {
            unlink($jsonPath);
        }
        if (file_exists($tempPngPath)) {
            unlink($tempPngPath);
        }
    }

}
