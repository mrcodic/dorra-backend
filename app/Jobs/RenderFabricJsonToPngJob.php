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
        // Save Fabric JSON temporarily
        $jsonPath = storage_path('app/fabric-json.json');
        file_put_contents($jsonPath, $this->fabricJson);

        // Temp PNG path
        $tempPngPath = storage_path('app/fabric-rendered.png');

        // Node script path
        $nodeScriptPath = base_path('fabric-renderer/renderFabric.js');
        $cmd = "node {$nodeScriptPath} {$jsonPath} {$tempPngPath}";

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            Log::error('Fabric render job failed', ['cmd' => $cmd, 'output' => $output]);
            throw new \Exception("Failed to render PNG from Fabric JSON");
        }

        // Attach PNG to model using media library
        $this->model->addMedia($tempPngPath)
            ->usingFileName('fabric_rendered_' . uniqid() . '.png')
            ->toMediaCollection($this->collectionName);


        // Cleanup temp files
        unlink($jsonPath);
        unlink($tempPngPath);
    }
}
