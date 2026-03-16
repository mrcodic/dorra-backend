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
    public $backoff = [5, 10, 30];
    public $timeout = 300; // 5 minutes timeout
    public $maxExceptions = 3;

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
        // Increase memory limit for this job
        $this->increaseMemoryLimit();

        try {
            Log::info('Processing base64 image', [
                'template_id' => $this->template->id ?? null,
                'collection' => $this->collection,
                'memory_limit' => ini_get('memory_limit'),
                'memory_usage' => $this->formatBytes(memory_get_usage(true))
            ]);

            // Extract and validate base64 data
            $imageData = $this->extractBase64Data($this->base64Image);

            if (!$imageData) {
                throw new \Exception('Failed to extract image data from base64 string');
            }

            // Check image size before processing
            $imageSize = strlen($imageData['data']);
            $maxSize = 50 * 1024 * 1024; // 50MB max

            if ($imageSize > $maxSize) {
                throw new \Exception("Image too large: " . $this->formatBytes($imageSize));
            }

            Log::info('Image data extracted', [
                'type' => $imageData['type'],
                'size' => $this->formatBytes($imageSize)
            ]);

            // Optimize image before saving
            $optimizedData = $this->optimizeImageData($imageData['data'], $imageData['type']);

            if (!$optimizedData) {
                throw new \Exception('Failed to optimize image data');
            }

            // Save to temp file
            $tempFilePath = $this->saveToTempFile($optimizedData, $imageData['type']);

            if (!$tempFilePath) {
                throw new \Exception('Failed to save image to temporary file');
            }

            // Clear memory
            unset($imageData, $optimizedData);

            // Verify the file is valid
            if (!$this->verifyImageFile($tempFilePath)) {
                throw new \Exception('Saved image file is corrupted or invalid');
            }

            // Clear existing media and add new one
            $this->template->clearMediaCollection($this->collection);

            // Force garbage collection
            gc_collect_cycles();

            $media = $this->template->addMedia($tempFilePath)
                ->withCustomProperties([
                    'original_type' => $imageData['type'],
                    'processed_at' => now()->toDateTimeString(),
                    'original_size' => $imageSize,
                    'optimized_size' => filesize($tempFilePath)
                ])
                ->toMediaCollection($this->collection);

            Log::info('Successfully added media', [
                'media_id' => $media->id,
                'template_id' => $this->template->id,
                'file_size' => $this->formatBytes(filesize($tempFilePath))
            ]);

            // Clean up temp file
            @unlink($tempFilePath);

            // Render mockups with memory management
            $this->renderMockupsWithMemoryManagement();

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }

            Log::error('ProcessBase64Image failed: ' . $e->getMessage(), [
                'template_id' => $this->template->id ?? null,
                'collection' => $this->collection,
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        } finally {
            // Reset memory limit if we changed it
            $this->resetMemoryLimit();
        }
    }

    /**
     * Increase memory limit for image processing
     */
    private function increaseMemoryLimit(): void
    {
        $currentLimit = ini_get('memory_limit');
        if ($currentLimit !== '-1') {
            // Try to set to 512M or unlimited
            @ini_set('memory_limit', '512M');
        }
    }

    /**
     * Reset memory limit
     */
    private function resetMemoryLimit(): void
    {
        // Optional: reset to original value if you stored it
    }

    /**
     * Extract data from base64 image string
     */
    private function extractBase64Data(string $base64Image): ?array
    {
        // Check if it's a valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,(.*)$/', $base64Image, $matches)) {
            $type = strtolower($matches[1]);
            $data = $matches[2];

            // Clean the data
            $data = preg_replace('/\s+/', '', $data);

            // Decode in chunks to manage memory
            $decoded = $this->base64DecodeChunked($data);

            if ($decoded !== false) {
                return [
                    'type' => $type,
                    'data' => $decoded
                ];
            }
        }

        return null;
    }

    /**
     * Decode base64 in chunks to manage memory
     */
    private function base64DecodeChunked(string $data): ?string
    {
        // If data is small, decode normally
        if (strlen($data) < 10 * 1024 * 1024) { // 10MB
            return base64_decode($data, true);
        }

        // For large data, decode in chunks
        $result = '';
        $chunkSize = 1024 * 1024; // 1MB chunks

        for ($i = 0; $i < strlen($data); $i += $chunkSize) {
            $chunk = substr($data, $i, $chunkSize);
            $result .= base64_decode($chunk, true);

            // Free memory
            unset($chunk);

            // Check memory usage
            if (memory_get_usage(true) > 100 * 1024 * 1024) { // 100MB
                Log::warning('High memory usage during base64 decode', [
                    'memory' => $this->formatBytes(memory_get_usage(true))
                ]);
            }
        }

        return $result;
    }

    /**
     * Optimize image data to reduce memory usage
     */
    private function optimizeImageData(string $data, string $type): ?string
    {
        try {
            // Create image resource
            $image = @imagecreatefromstring($data);

            if ($image === false) {
                return $data; // Return original if can't create
            }

            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // If image is too large, resize it
            $maxDimension = 2000;
            $newWidth = $width;
            $newHeight = $height;

            if ($width > $maxDimension || $height > $maxDimension) {
                if ($width > $height) {
                    $newWidth = $maxDimension;
                    $newHeight = (int)($height * ($maxDimension / $width));
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int)($width * ($maxDimension / $height));
                }

                // Create new image
                $newImage = imagecreatetruecolor($newWidth, $newHeight);

                // Preserve transparency for PNG
                if ($type === 'png') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                // Resize
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                // Destroy original
                imagedestroy($image);
                $image = $newImage;

                Log::info('Image resized', [
                    'original' => "{$width}x{$height}",
                    'new' => "{$newWidth}x{$newHeight}"
                ]);
            }

            // Output to string
            ob_start();
            switch ($type) {
                case 'png':
                    // Reduce quality slightly for PNG
                    imagepng($image, null, 7, PNG_ALL_FILTERS);
                    break;
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, null, 85);
                    break;
                case 'gif':
                    imagegif($image);
                    break;
                default:
                    imagepng($image, null, 7, PNG_ALL_FILTERS);
            }

            $optimized = ob_get_clean();
            imagedestroy($image);

            // Force garbage collection
            gc_collect_cycles();

            return $optimized;

        } catch (\Exception $e) {
            Log::warning('Image optimization failed: ' . $e->getMessage());
            return $data; // Return original if optimization fails
        }
    }

    /**
     * Save data to temporary file
     */
    private function saveToTempFile(string $data, string $type): ?string
    {
        $tempDir = storage_path('app/tmp_uploads');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFilePath = $tempDir . '/' . uniqid('img_', true) . '.' . $type;

        // Write in chunks to manage memory
        $handle = fopen($tempFilePath, 'wb');
        if ($handle === false) {
            return null;
        }

        $chunkSize = 1024 * 1024; // 1MB chunks
        for ($i = 0; $i < strlen($data); $i += $chunkSize) {
            $chunk = substr($data, $i, $chunkSize);
            fwrite($handle, $chunk);
            unset($chunk);
        }

        fclose($handle);

        // Free the original data
        unset($data);

        return $tempFilePath;
    }

    /**
     * Verify image file with memory-efficient approach
     */
    private function verifyImageFile(string $filePath): bool
    {
        if (!file_exists($filePath) || filesize($filePath) === 0) {
            return false;
        }

        // Check if file is too large
        $maxFileSize = 100 * 1024 * 1024; // 100MB
        if (filesize($filePath) > $maxFileSize) {
            Log::error('Image file too large', ['size' => filesize($filePath)]);
            return false;
        }

        // Try to get image info without loading entire file
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }

        // Check dimensions
        if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
            Log::warning('Very large image dimensions', [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1]
            ]);
        }

        return true;
    }

    /**
     * Render mockups with memory management
     */
    private function renderMockupsWithMemoryManagement(): void
    {
        $template = $this->template->fresh(['mockups.types', 'mockups.media']);

        $mockups = $template->mockups;

        if (!$mockups || $mockups->isEmpty()) return;

        foreach ($mockups as $index => $mockup) {
            // Clear memory every few iterations
            if ($index > 0 && $index % 2 === 0) {
                gc_collect_cycles();
            }

            $this->renderSingleMockup($mockup, $template);
        }
    }

    /**
     * Render a single mockup with memory management
     */
    private function renderSingleMockup($mockup, $template): void
    {
        $positions = $mockup->pivot->positions ?? [];

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

            try {
                // Check memory before processing
                if (memory_get_usage(true) > 100 * 1024 * 1024) { // 100MB
                    Log::warning('High memory usage before render', [
                        'memory' => $this->formatBytes(memory_get_usage(true))
                    ]);
                    gc_collect_cycles();
                }

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

                // Render with memory limit
                $binary = $this->renderWithMemoryLimit([
                    'base_path'   => $base->getPath(),
                    'shirt_path'  => $mask->getPath(),
                    'design_path' => $design->getPath(),
                    'print_x'     => $printX,
                    'print_y'     => $printY,
                    'print_w'     => $printW,
                    'print_h'     => $printH,
                    'angle'       => $angle,
                ]);

                if ($binary) {
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
                }

                // Clear binary data
                unset($binary);

            } catch (\Throwable $e) {
                Log::error("Render failed mockup {$mockup->id} side {$side}: " . $e->getMessage());
            } finally {
                // Force garbage collection
                gc_collect_cycles();
            }
        }
    }

    /**
     * Render with memory limit protection
     */
    private function renderWithMemoryLimit(array $params): ?string
    {
        try {
            return (new MockupRenderer())->render($params);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Allowed memory size')) {
                Log::error('Memory limit exceeded during render', [
                    'memory_usage' => $this->formatBytes(memory_get_usage(true))
                ]);

                // Try one more time with lower quality settings
                return $this->renderWithLowQuality($params);
            }
            throw $e;
        }
    }

    /**
     * Render with lower quality to save memory
     */
    private function renderWithLowQuality(array $params): ?string
    {
        try {
            // You can implement a lower quality version here
            // For now, return null
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
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
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'trace' => $e->getTraceAsString()
        ]);

        // Mark template as failed
        if ($this->template && method_exists($this->template, 'update')) {
            try {
                $this->template->update([
                    'processing_failed' => true,
                    'failed_at' => now(),
                    'error_message' => substr($e->getMessage(), 0, 255)
                ]);
            } catch (\Exception $updateError) {
                Log::error('Failed to update template status: ' . $updateError->getMessage());
            }
        }
    }
}
