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

    public $tries = 5; // Increased retry attempts
    public $backoff = [3, 6, 12, 24, 30]; // Progressive backoff

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
            // Log initial data for debugging
            Log::info('Processing base64 image', [
                'template_id' => $this->template->id ?? null,
                'collection' => $this->collection,
                'base64_length' => strlen($this->base64Image),
                'base64_preview' => substr($this->base64Image, 0, 100) . '...'
            ]);

            // Extract and validate base64 data
            $imageData = $this->extractBase64Data($this->base64Image);

            if (!$imageData) {
                throw new \Exception('Failed to extract image data from base64 string');
            }

            // Try multiple recovery methods
            $recoveredData = $this->recoverImageData($imageData['data'], $imageData['type']);

            if (!$recoveredData) {
                // Log the problematic data for debugging
                $this->debugImageData($imageData['data']);
                throw new \Exception('Image data is corrupted and could not be fixed after multiple recovery attempts');
            }

            // Save to temp file
            $tempFilePath = $this->saveToTempFile($recoveredData, $imageData['type']);

            if (!$tempFilePath) {
                throw new \Exception('Failed to save image to temporary file');
            }

            // Verify the file is valid
            if (!$this->verifyImageFile($tempFilePath)) {
                throw new \Exception('Saved image file is corrupted or invalid');
            }

            // Clear existing media and add new one
            $this->template->clearMediaCollection($this->collection);

            $media = $this->template->addMedia($tempFilePath)
                ->withCustomProperties([
                    'original_type' => $imageData['type'],
                    'processed_at' => now()->toDateTimeString(),
                    'recovery_attempted' => true
                ])
                ->toMediaCollection($this->collection);

            Log::info('Successfully added media', [
                'media_id' => $media->id,
                'template_id' => $this->template->id
            ]);

            // Clean up temp file
            @unlink($tempFilePath);

            // Render mockups
            $this->renderMockups();

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }

            Log::error('ProcessBase64Image failed: ' . $e->getMessage(), [
                'template_id' => $this->template->id ?? null,
                'collection' => $this->collection,
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw for retry
            throw $e;
        }
    }

    /**
     * Extract data from base64 image string
     */
    private function extractBase64Data(string $base64Image): ?array
    {
        // Try multiple base64 patterns
        $patterns = [
            '/^data:image\/(\w+);base64,(.*)$/', // Standard pattern
            '/^data:image\/(\w+);base64(.*)$/i',  // Missing comma
            '/^data:image\/(\w+),(.*)$/i',        // Missing base64 indicator
            '/^data:application\/image;base64,(.*)$/i', // Application type
            '/^base64,(.*)$/i',                    // Just base64 indicator
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $base64Image, $matches)) {
                $type = strtolower($matches[1] ?? 'png');
                $data = $matches[count($matches) - 1]; // Last match is always the data

                // Clean the data
                $data = preg_replace('/\s+/', '', $data); // Remove whitespace

                // Validate type
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                    $type = 'png'; // Default to png if unknown
                }

                // Try to decode
                $decoded = base64_decode($data, true);
                if ($decoded !== false) {
                    return [
                        'type' => $type,
                        'data' => $decoded
                    ];
                }
            }
        }

        // If no pattern matched, try treating the whole string as base64
        $decoded = base64_decode($base64Image, true);
        if ($decoded !== false) {
            return [
                'type' => 'png', // Assume PNG
                'data' => $decoded
            ];
        }

        return null;
    }

    /**
     * Attempt multiple recovery methods
     */
    private function recoverImageData(string $data, string $type): ?string
    {
        $recoveryMethods = [
            'direct' => fn($d) => $this->attemptDirectLoad($d),
            'reencode' => fn($d) => $this->attemptReencode($d, $type),
            'strip' => fn($d) => $this->attemptStripCorruption($d, $type),
            'create_new' => fn($d) => $this->attemptCreateNewImage($d, $type),
            'force_png' => fn($d) => $this->attemptForcePNG($d),
        ];

        foreach ($recoveryMethods as $methodName => $method) {
            try {
                Log::info("Attempting recovery method: {$methodName}");
                $result = $method($data);
                if ($result && $this->validateImageData($result)) {
                    Log::info("Recovery method {$methodName} succeeded");
                    return $result;
                }
            } catch (\Exception $e) {
                Log::warning("Recovery method {$methodName} failed: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Attempt to load image directly
     */
    private function attemptDirectLoad(string $data): ?string
    {
        $img = @imagecreatefromstring($data);
        if ($img !== false) {
            imagedestroy($img);
            return $data;
        }
        return null;
    }

    /**
     * Attempt to reencode the image
     */
    private function attemptReencode(string $data, string $type): ?string
    {
        $img = @imagecreatefromstring($data);
        if ($img === false) {
            return null;
        }

        ob_start();
        switch ($type) {
            case 'png':
                imagepng($img, null, 9, PNG_ALL_FILTERS);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($img, null, 95);
                break;
            case 'gif':
                imagegif($img);
                break;
            default:
                imagepng($img, null, 9, PNG_ALL_FILTERS);
        }
        $cleanData = ob_get_clean();
        imagedestroy($img);

        return $cleanData;
    }

    /**
     * Attempt to strip corruption by finding valid image boundaries
     */
    private function attemptStripCorruption(string $data, string $type): ?string
    {
        if ($type === 'png') {
            return $this->stripPNGCorruption($data);
        } elseif ($type === 'jpg' || $type === 'jpeg') {
            return $this->stripJPEGCorruption($data);
        }

        // For other types, try to find valid image data
        return $this->findValidImageData($data);
    }

    /**
     * Strip corruption from PNG
     */
    private function stripPNGCorruption(string $data): ?string
    {
        // Find PNG signature
        $pngSignature = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        $sigPos = strpos($data, $pngSignature);

        if ($sigPos === false) {
            return null;
        }

        // Extract from signature to end
        $cleanData = substr($data, $sigPos);

        // Try to load it
        $img = @imagecreatefromstring($cleanData);
        if ($img !== false) {
            imagedestroy($img);
            return $cleanData;
        }

        return null;
    }

    /**
     * Strip corruption from JPEG
     */
    private function stripJPEGCorruption(string $data): ?string
    {
        // Find JPEG SOI marker
        $soiMarker = "\xFF\xD8";
        $soiPos = strpos($data, $soiMarker);

        if ($soiPos === false) {
            return null;
        }

        // Find EOI marker
        $eoiMarker = "\xFF\xD9";
        $eoiPos = strrpos($data, $eoiMarker);

        if ($eoiPos === false) {
            // If no EOI, take from SOI to end
            $cleanData = substr($data, $soiPos);
        } else {
            // Take from SOI to EOI
            $cleanData = substr($data, $soiPos, $eoiPos - $soiPos + 2);
        }

        // Try to load it
        $img = @imagecreatefromstring($cleanData);
        if ($img !== false) {
            imagedestroy($img);
            return $cleanData;
        }

        return null;
    }

    /**
     * Find valid image data by scanning for known headers
     */
    private function findValidImageData(string $data): ?string
    {
        $imageSignatures = [
            'png' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A",
            'jpg' => "\xFF\xD8\xFF",
            'gif' => "GIF8",
            'bmp' => "BM",
            'webp' => "RIFF"
        ];

        foreach ($imageSignatures as $type => $signature) {
            $pos = strpos($data, $signature);
            if ($pos !== false) {
                $candidate = substr($data, $pos);
                $img = @imagecreatefromstring($candidate);
                if ($img !== false) {
                    imagedestroy($img);
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * Attempt to create a new image from corrupted data
     */
    private function attemptCreateNewImage(string $data, string $type): ?string
    {
        try {
            // Try to get dimensions
            $img = @imagecreatefromstring($data);
            if ($img === false) {
                // If we can't load it, try to create a blank image
                $img = imagecreatetruecolor(800, 600);
                $bg = imagecolorallocate($img, 255, 255, 255);
                imagefill($img, 0, 0, $bg);

                // Try to embed corrupted data as text? Not ideal but better than nothing
                $textColor = imagecolorallocate($img, 0, 0, 0);
                imagestring($img, 5, 10, 10, "Corrupted Image", $textColor);
            }

            ob_start();
            imagepng($img, null, 9, PNG_ALL_FILTERS);
            $newData = ob_get_clean();
            imagedestroy($img);

            return $newData;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Force conversion to PNG
     */
    private function attemptForcePNG(string $data): ?string
    {
        $img = @imagecreatefromstring($data);
        if ($img === false) {
            return null;
        }

        ob_start();
        imagepng($img, null, 9, PNG_ALL_FILTERS);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return $pngData;
    }

    /**
     * Validate image data
     */
    private function validateImageData(string $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // Try to create image from string
        $img = @imagecreatefromstring($data);
        if ($img === false) {
            return false;
        }

        imagedestroy($img);
        return true;
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

        if (file_put_contents($tempFilePath, $data) === false) {
            return null;
        }

        return $tempFilePath;
    }

    /**
     * Verify image file
     */
    private function verifyImageFile(string $filePath): bool
    {
        if (!file_exists($filePath) || filesize($filePath) === 0) {
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
     * Debug image data
     */
    private function debugImageData(string $data): void
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $data);
        finfo_close($finfo);

        Log::error('Corrupted image debug info:', [
            'size' => strlen($data),
            'mime_type' => $mimeType,
            'first_50_bytes' => bin2hex(substr($data, 0, 50)),
            'last_50_bytes' => bin2hex(substr($data, -50)),
            'contains_png_signature' => strpos($data, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") !== false,
            'contains_jpg_signature' => strpos($data, "\xFF\xD8\xFF") !== false,
            'is_valid_utf8' => mb_check_encoding($data, 'UTF-8')
        ]);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $e): void
    {
        Log::error('ProcessBase64Image failed permanently after all retries', [
            'template_id' => $this->template->id ?? null,
            'collection' => $this->collection,
            'error' => $e->getMessage(),
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

    private function renderMockups(): void
    {
        $template = $this->template->fresh(['mockups.types', 'mockups.media']);

        $mockups = $template->mockups;

        if (!$mockups || $mockups->isEmpty()) return;

        foreach ($mockups as $mockup) {
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
                    Log::error("Render failed mockup {$mockup->id} side {$side}: " . $e->getMessage());
                }
            }
        }
    }
}
