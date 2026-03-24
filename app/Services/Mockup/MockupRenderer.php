<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;

class MockupRenderer
{
    public function render(array $options): string
    {
        // 1. Setup Options
        $basePath   = $options['base_path'];
        $maskPath   = $options['shirt_path'];
        $designPath = $options['design_path'] ?? null;
        $hex        = $options['hex'] ?? '#ffffff';

        $printX = (int)($options['print_x'] ?? 360);
        $printY = (int)($options['print_y'] ?? 660);
        $printW = max(1, (int)($options['print_w'] ?? 480));
        $printH = max(1, (int)($options['print_h'] ?? 540));
        $maxDim = (int)($options['max_dim'] ?? 800);
        $angle  = (float)($options['angle'] ?? 0);

        // 2. Load Assets
        $base = Image::read($basePath);
        $mask = Image::read($maskPath);

        // 3. Create Tinted Layer (CLONE the mask to preserve transparency)
        $tintedShirt = $this->tintShirt($mask, $hex);

        // 4. Compose: Place tinted shirt onto the model base
        $canvas = clone $base;
        $canvas->place($tintedShirt, 'top-left', 0, 0);

        // 5. Place Design
        if ($designPath && file_exists($designPath)) {
            $design = Image::read($designPath);
            $design->scaleDown(width: $printW, height: $printH);

            if ($angle != 0) {
                $design = $design->rotate(-(float)$angle, 'transparent');
            }

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
        }

        // 6. Scale for Web
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        return $canvas->toPng()->toString();
    }

    /**
     * Tints only the shirt pixels by using the Mask as the base.
     * Includes logic for Black, White, and Vivid Colors.
     */
    public function tintShirt(ImageInterface $mask, string $hex): ImageInterface
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Calculate perceived brightness (0–255)
        $brightnessValue = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);

        // Start with the mask itself to keep the transparent background
        $img = clone $mask;
        $img->greyscale();

        // --- CASE 1: Pure / Near-Black (#000000) ---
        if ($brightnessValue < 25) {
            $img->brightness(-60)
                ->contrast(20);
            return $img;
        }

        // --- CASE 2: Pure / Near-White (#FFFFFF) ---
        if ($brightnessValue > 235) {
            $img->brightness(50)
                ->contrast(-5);
            return $img;
        }

        // --- CASE 3: All other colors ---
        // Map RGB 0..255 -> -100..100 range for vivid results
        $map = function (int $c): int {
            return (int)round((($c - 128) / 128) * 100);
        };

        $rAdj = $map($r);
        $gAdj = $map($g);
        $bAdj = $map($b);

        // Adjust overall brightness based on color luminance
        $brightnessShift = (int)(($brightnessValue - 128) / 128 * 25);

        $img->colorize($rAdj, $gAdj, $bAdj)
            ->contrast(12)
            ->brightness($brightnessShift);

        return $img;
    }
}
