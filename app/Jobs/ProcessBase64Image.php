<?php

namespace App\Jobs;

use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
use App\Traits\RendersTemplateMockups;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBase64Image implements ShouldQueue
{
    use Queueable,RendersTemplateMockups;

    public function __construct(
        public string $base64Image,
        public $template,
        public $collection = null
    ) {}

    public function handle(): void
    {
        ini_set('memory_limit', '512M');

        // ---- 1) Decode base64 ----
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

        // Free raw string immediately
        unset($imageData);

        // ---- 2) Validate before passing to Spatie ----
        if (!$this->isValidImage($tempFilePath)) {
            @unlink($tempFilePath);
            throw new \Exception('Written temp file is not a valid image');
        }

        // ---- 3) Save to media collection ----
        $this->template->clearMediaCollection($this->collection);
        $this->template->addMedia($tempFilePath)
            ->toMediaCollection($this->collection);

        // Spatie moves the file — no manual unlink needed
        unset($tempFilePath);

        // Force GC before heavy rendering
        gc_collect_cycles();

        // ---- 4) Render mockups ----
        if (get_class($this->template) === Template::class) {
            $this->renderMockups($this->template, $this->collection);
        }
    }

    private function isValidImage(string $path): bool
    {
        if (!file_exists($path) || filesize($path) === 0) return false;

        return @getimagesize($path) !== false;
    }

}
