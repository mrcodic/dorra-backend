<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;

class MockupRenderer
{
    public function render(array $options): string
    {
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

        // 1. Load the Base (The background/model)
        $canvas = Image::read($basePath);

        // 2. Load the Mask (The shirt shape)
        $mask = Image::read($maskPath);

        // 3. Create the Tinted Shirt
        $tintedShirt = $this->createTintedLayer($mask, $hex);

        // 4. Place the tinted shirt ONLY where the shirt is
        $canvas->place($tintedShirt, 'top-left', 0, 0);

        // 5. Place Design
        if ($designPath && file_exists($designPath)) {
            $design = Image::read($designPath);
            $design->scaleDown(width: $printW, height: $printH);

            if ($angle != 0) {
                $design->rotate(-(float)$angle, 'transparent');
            }

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
        }

        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        return $canvas->toPng()->toString();
    }

    /**
     * THE FIX: This ensures the color stays INSIDE the shirt shape.
     */
    private function createTintedLayer(ImageInterface $mask, string $hex): ImageInterface
    {
        // 1. Create a clone of the mask to act as our "Base"
        // This ensures we keep the transparency of the original mask file
        $tintedShirt = clone $mask;

        // 2. Use a "Colorize" or "Overlay" effect on the shirt pixels only
        // Since we are using v3, we can't use mask(), so we use 'colorize'
        // because it only affects non-transparent pixels.

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Map RGB to the -100 to 100 range
        $rAdj = (int)round((($r - 128) / 128) * 100);
        $gAdj = (int)round((($g - 128) / 128) * 100);
        $bAdj = (int)round((($b - 128) / 128) * 100);

        // Apply greyscale to the shirt first to clear existing colors
        // Then colorize only the shirt pixels, preserving the texture
        $tintedShirt->greyscale()
            ->colorize($rAdj, $gAdj, $bAdj)
            ->contrast(15);

        return $tintedShirt;
    }
}
