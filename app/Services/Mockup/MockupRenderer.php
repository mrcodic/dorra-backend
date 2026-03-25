<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Log;

class MockupRenderer
{
    /**
     * Render a mockup with:
     * - base image (model wearing shirt)
     * - shirt PNG (transparent mask / overlay)
     * - optional design to place on shirt
     * - tint color (HEX)
     * - configurable print box & size
     */
    public function render(array $options): string
    {
        // ----- 1) Read options with sane defaults -----
        $basePath   = $options['base_path'];           // required
        $shirtPath  = $options['shirt_path'];          // required
        $designPath = $options['design_path'] ?? null;
        $hex        = $options['hex'] ?? null;

        $printX = (int)($options['print_x'] ?? 360);
        $printY = (int)($options['print_y'] ?? 660);
        $printW = max(1, (int)($options['print_w'] ?? 480));
        $printH = max(1, (int)($options['print_h'] ?? 540));
        $maxDim = (int)($options['max_dim'] ?? 800);
        $angle  = (float)($options['angle'] ?? 0);

        // ----- 2) Validate paths -----
        if (!file_exists($basePath)) {
            throw new \InvalidArgumentException("Base image not found: {$basePath}");
        }
        if (!file_exists($shirtPath)) {
            throw new \InvalidArgumentException("Shirt/mask image not found: {$shirtPath}");
        }

        // ----- LOG: file sizes & dimensions before anything is loaded -----
        $this->logImageInfo('base',   $basePath);
        $this->logImageInfo('shirt',  $shirtPath);
        if ($designPath) {
            $this->logImageInfo('design', $designPath);
        }
        $this->logMemory('start');

        // ----- 3) Tint the shirt (reads fresh from disk — no clone) -----
        $tintedShirt = (!empty($hex))
            ? $this->tintShirt($shirtPath, $hex)
            : Image::read($shirtPath);

        $this->logMemory('after tintShirt');

        // ----- 4) Compose canvas: fresh base read + tinted shirt -----
        $canvas = Image::read($basePath);
        $this->logMemory('after base read');

        $canvas->place($tintedShirt, 'top-left', 0, 0);
        unset($tintedShirt); // free GD resource immediately
        $this->logMemory('after place shirt + unset');

        // ----- 5) Place design if provided -----
        if ($designPath) {
            if (!file_exists($designPath)) {
                throw new \InvalidArgumentException("Design image not found: {$designPath}");
            }

            $design = Image::read($designPath);
            $this->logMemory('after design read');

            // Scale design to fit inside the print box
            $design->scaleDown(width: $printW, height: $printH);

            // Rotate around center, keeping alpha channel
            if (!empty($angle)) {
                $design = $design->rotate(-(float)$angle, 'transparent');
                $this->logMemory('after design rotate');
            }

            // Center the design horizontally within the print box
            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
            unset($design); // free GD resource immediately
            $this->logMemory('after place design + unset');
        }

        // ----- 6) Scale down for web -----
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
            $this->logMemory('after scaleDown');
        }

        // ----- 7) Return encoded PNG -----
        $result = $canvas->toPng()->toString();
        unset($canvas);
        $this->logMemory('after encode + unset canvas');

        return $result;
    }

    /**
     * Tint shirt PNG with HEX color, preserving fabric texture/folds.
     *
     * Reads fresh from disk instead of cloning to avoid GD memory duplication.
     */
    public function tintShirt(string $shirtPath, string $hex): mixed
    {
        $hex = ltrim($hex, '#');

        // Pad shorthand hex (e.g. "fff" -> "ffffff")
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Perceived brightness (0–255)
        $brightness = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);

        // Read fresh from disk — avoids cloning the GD bitmap into memory
        $img = Image::read($shirtPath);
        $this->logMemory('tintShirt: after read');

        $img->greyscale();
        $this->logMemory('tintShirt: after greyscale');

        // ── Pure / near-black (brightness < 20) ──────────────────────────
        if ($brightness < 20) {
            $img->brightness(-55)->contrast(15);
            $this->logMemory('tintShirt: after black adjust');
            return $img;
        }

        // ── Pure / near-white (brightness > 235) ─────────────────────────
        if ($brightness > 235) {
            $img->brightness(55)->contrast(-10);
            $this->logMemory('tintShirt: after white adjust');
            return $img;
        }

        // ── All other colors ──────────────────────────────────────────────
        $map = function (int $c): int {
            return (int)round((($c - 128) / 128) * 100);
        };

        $rAdj = $map($r);
        $gAdj = $map($g);
        $bAdj = $map($b);

        $brightnessShift = (int)(($brightness - 128) / 128 * 30);

        $img->colorize($rAdj, $gAdj, $bAdj)
            ->contrast(10)
            ->brightness($brightnessShift);

        $this->logMemory('tintShirt: after colorize');

        return $img;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function logImageInfo(string $label, string $path): void
    {
        $info = @getimagesize($path);
        $size = round(filesize($path) / 1024, 2);

        // GD memory formula: width * height * 4 bytes (RGBA)
        $estimatedMB = $info
            ? round(($info[0] * $info[1] * 4) / 1024 / 1024, 2)
            : null;

        Log::debug("Image [{$label}]", [
            'path'          => $path,
            'file_size_kb'  => $size,
            'dimensions'    => $info ? $info[0] . 'x' . $info[1] : 'unknown',
            'gd_memory_est' => $estimatedMB ? "{$estimatedMB} MB" : 'unknown',
        ]);
    }

    private function logMemory(string $checkpoint): void
    {
        Log::debug("Memory [{$checkpoint}]", [
            'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_mb'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ]);
    }
}
