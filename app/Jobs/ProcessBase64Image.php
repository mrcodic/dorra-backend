<?php

namespace App\Jobs;

use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBase64Image implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [2, 5, 10]; // Seconds between retries

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
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $this->base64Image, $type)) {
                $imageData = substr($this->base64Image, strpos($this->base64Image, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new \Exception('Invalid image type');
                }

                // Use strict mode for base64 decoding
                $imageData = base64_decode($imageData, true);
                if ($imageData === false) {
                    throw new \Exception('base64_decode failed');
                }

                // Validate image data before processing
                if (!$this->validateImageData($imageData, $type)) {
                    // Attempt to fix the image if validation fails
                    $imageData = $this->fixImageData($imageData, $type);
                    if (!$imageData) {
                        throw new \Exception('Image data is corrupted and could not be fixed');
                    }
                }

                // Verify image can be loaded by GD
                $gdImage = @imagecreatefromstring($imageData);
                if ($gdImage === false) {
                    throw new \Exception('Image data is corrupted or invalid (GD library cannot load it)');
                }
                imagedestroy($gdImage);

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

            // Optimize the image to ensure it's clean
            $this->optimizeImage($tempFilePath, $type);

            // Verify the saved file can be read
            if (!$this->verifyImageFile($tempFilePath)) {
                throw new \Exception('Saved image file is corrupted');
            }

            $this->template->clearMediaCollection($this->collection);
            $this->template->addMedia($tempFilePath)
                ->toMediaCollection($this->collection);

            // Uncomment if you want to delete temp file after processing
            // @unlink($tempFilePath);

            $this->renderMockups();

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }

            Log::error('ProcessBase64Image failed: ' . $e->getMessage(), [
                'template_id' => $this->template->id ?? null,
                'collection' => $this->collection,
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw for retry
            throw $e;
        }
    }

    /**
     * Validate image data integrity
     */
    private function validateImageData(string $data, string $type): bool
    {
        // Check if data is empty
        if (empty($data)) {
            Log::error('Image data is empty');
            return false;
        }

        // For PNG files, validate the signature
        if ($type === 'png') {
            return $this->validatePNG($data);
        }

        // For JPEG files, validate the SOI marker
        if ($type === 'jpg' || $type === 'jpeg') {
            return $this->validateJPEG($data);
        }

        // Try to create image from string as final validation
        $img = @imagecreatefromstring($data);
        if ($img === false) {
            return false;
        }
        imagedestroy($img);

        return true;
    }

    /**
     * Validate PNG integrity by checking the signature and IHDR chunk
     */
    private function validatePNG(string $data): bool
    {
        // Check PNG signature (first 8 bytes)
        $pngSignature = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        if (substr($data, 0, 8) !== $pngSignature) {
            Log::error('PNG signature validation failed', [
                'expected' => bin2hex($pngSignature),
                'actual' => bin2hex(substr($data, 0, 8))
            ]);
            return false;
        }

        // Check for IHDR chunk (must be first chunk)
        $ihdrChunk = substr($data, 8, 4);
        if ($ihdrChunk !== 'IHDR') {
            Log::error('PNG IHDR chunk missing');
            return false;
        }

        return true;
    }

    /**
     * Validate JPEG integrity
     */
    private function validateJPEG(string $data): bool
    {
        // Check JPEG SOI marker (FF D8)
        if (bin2hex(substr($data, 0, 2)) !== 'ffd8') {
            Log::error('JPEG SOI marker missing');
            return false;
        }

        // Check for EOI marker (FF D9) at the end
        if (bin2hex(substr($data, -2)) !== 'ffd9') {
            Log::warn('JPEG EOI marker missing - image may be truncated');
            // Don't return false as some JPEGs might still work
        }

        return true;
    }

    /**
     * Attempt to fix corrupted image data by re-encoding it
     */
    private function fixImageData(string $data, string $type): ?string
    {
        try {
            Log::info('Attempting to fix corrupted image', ['type' => $type]);

            // Try to load the corrupted image
            $img = @imagecreatefromstring($data);
            if ($img === false) {
                return null;
            }

            // Re-encode to a clean image
            ob_start();
            switch ($type) {
                case 'png':
                    imagepng($img, null, 9, PNG_ALL_FILTERS);
                    break;
                case 'jpg':
                case 'jpeg':
                    imagejpeg($img, null, 90);
                    break;
                case 'gif':
                    imagegif($img);
                    break;
                default:
                    imagedestroy($img);
                    return null;
            }
            $cleanData = ob_get_clean();
            imagedestroy($img);

            if ($cleanData && $this->validateImageData($cleanData, $type)) {
                Log::info('Successfully fixed corrupted image');
                return $cleanData;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to fix image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Optimize and clean image file
     */
    private function optimizeImage(string $filePath, string $type): void
    {
        try {
            if (!file_exists($filePath)) {
                return;
            }

            // Load the image based on type
            $img = null;
            switch ($type) {
                case 'png':
                    $img = @imagecreatefrompng($filePath);
                    if ($img) {
                        // Save with maximum compatibility and compression
                        imagepng($img, $filePath, 9, PNG_ALL_FILTERS);
                    }
                    break;

                case 'jpg':
                case 'jpeg':
                    $img = @imagecreatefromjpeg($filePath);
                    if ($img) {
                        // Save with good quality
                        imagejpeg($img, $filePath, 90);
                    }
                    break;

                case 'gif':
                    $img = @imagecreatefromgif($filePath);
                    if ($img) {
                        imagegif($img, $filePath);
                    }
                    break;
            }

            if ($img) {
                imagedestroy($img);
                Log::info('Image optimized successfully', ['file' => basename($filePath)]);
            }

        } catch (\Exception $e) {
            Log::warning('Image optimization failed: ' . $e->getMessage());
            // Don't throw, continue with original file
        }
    }

    /**
     * Verify saved image file can be read
     */
    private function verifyImageFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        // Try to get image info
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }

        // Try to load with GD
        $img = @imagecreatefromstring(file_get_contents($filePath));
        if ($img === false) {
            return false;
        }

        imagedestroy($img);
        return true;
    }

    /**
     * Debug image data (useful for troubleshooting)
     */
    private function debugImageData(string $data): void
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $data);
        finfo_close($finfo);

        Log::info('Image debug info:', [
            'size' => strlen($data),
            'mime_type' => $mimeType,
            'starts_with_png_signature' => substr($data, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'first_20_bytes' => bin2hex(substr($data, 0, 20)),
            'last_20_bytes' => bin2hex(substr($data, -20))
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $e): void
    {
        Log::error('ProcessBase64Image failed permanently', [
            'template_id' => $this->template->id ?? null,
            'collection' => $this->collection,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Optionally mark template as failed
        if ($this->template && method_exists($this->template, 'update')) {
            try {
                $this->template->update(['processing_failed' => true]);
            } catch (\Exception $updateError) {
                Log::error('Failed to update template status: ' . $updateError->getMessage());
            }
        }
    }

    private function renderMockups(): void
    {
        $template = $this->template->fresh(['mockups.types', 'mockups.media']);

        $mockups = $template->mockups; // pivot data comes with the relation

        if (!$mockups || $mockups->isEmpty()) return;

        foreach ($mockups as $mockup) {

            // ---- Read positions from pivot ----
            $positions = $mockup->pivot->positions ?? [];

            // Decode if stored as JSON string
            if (is_string($positions)) {
                $positions = json_decode($positions, true) ?? [];
            }

            foreach ($mockup->types as $type) {
                $side = strtolower($type->value->name);

                $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';
                if ($this->collection !== $expectedCollection) continue;

                $base = $mockup->getMedia('mockups')
                    ->first(fn($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'base'
                    );

                $mask = $mockup->getMedia('mockups')
                    ->first(fn($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'mask'
                    );

                if (!$base || !$mask) continue;

                $design = $template->getFirstMedia($this->collection);

                if (!$design || !file_exists($design->getPath())) continue;

                [$baseW, $baseH] = getimagesize($base->getPath());

                $xPct  = (float)($positions["{$side}_x"]      ?? 0.5);
                $yPct  = (float)($positions["{$side}_y"]      ?? 0.5);
                $wPct  = (float)($positions["{$side}_width"]  ?? 0.4);
                $hPct  = (float)($positions["{$side}_height"] ?? 0.4);
                $angle = (float)($positions["{$side}_angle"]  ?? 0);

                $printW = max(1, (int)round($wPct * $baseW));
                $printH = max(1, (int)round($hPct * $baseH));
                $printX = (int)round($xPct * $baseW - $printW / 2);
                $printY = (int)round($yPct * $baseH - $printH / 2);

                try {
                    $binary = (new MockupRenderer())->render([
                        'base_path'   => $base->getPath(),
                        'shirt_path'  => $mask->getPath(),
                        'design_path' => $design->getPath(),
                        'print_x'     => $printX,
                        'print_y'     => $printY,
                        'print_w'     => $printW,
                        'print_h'     => $printH,
                        'angle'       => $angle,
                    ]);

                    $template->getMedia('rendered_mockups')
                        ->filter(fn($m) =>
                            $m->getCustomProperty('side') === $side &&
                            $m->getCustomProperty('template_id') === $template->id
                        )
                        ->each->delete();

                    $template->addMediaFromString($binary)
                        ->usingFileName("tpl_{$side}_cat{$mockup->category_id}.png")
                        ->withCustomProperties([
                            'side'        => $side,
                            'template_id' => $template->id,
                            'category_id' => $mockup->category_id,
                        ])
                        ->toMediaCollection('rendered_mockups');

                } catch (\Throwable $e) {
                    Log::error("Render failed mockup {$mockup->id} side {$side} template {$template->id}: " . $e->getMessage());
                }
            }
        }
    }
}
