<?php

namespace App\Jobs;


use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBase64Image implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $base64Image, public $template, public $collection = null)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        dd($this->base64Image);
            if (preg_match('/^data:image\/(\w+);base64,/', $this->base64Image, $type)) {
            $imageData = substr($this->base64Image, strpos($this->base64Image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Invalid image type');
            }

            $imageData = base64_decode($imageData);
            if ($imageData === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('Invalid base64 format');
        }

        $tempDir = storage_path('app/tmp_uploads');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFilePath = $tempDir . '/' . uniqid('preview_') . '.' . $type;

        if (file_put_contents($tempFilePath, $imageData) === false) {
            throw new \Exception('Failed to write temp file');
        }
        $this->template->clearMediaCollection($this->collection);
        $this->template->addMedia($tempFilePath)
            ->toMediaCollection($this->collection);

        @unlink($tempFilePath);

    }

}
