<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\ImageInterface;

class MockupRenderer
{
    public function render(array $options): string
    {
        // 1. Setup Paths & Options
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

        // 2. Load Assets using v3 "read" syntax
        $base = Image::read($basePath);
        $mask = Image::read($maskPath);

        // 3. Create the Tinted Layer (The Fix for missing mask())
        $tintedShirt = $this->createTintedLayer($mask, $hex);

        // 4. Compose: Model + Tinted Shirt
        $canvas = clone $base;
        $canvas->place($tintedShirt, 'top-left', 0, 0);

        // 5. Place Design
        if ($designPath && file_exists($designPath)) {
            $design = Image::read($designPath);
            $design->scaleDown(width: $printW, height: $printH);

            if ($angle != 0) {
                $design->rotate(-(float)$angle, 'transparent');
            }

            // Center design in the print box
            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
        }

        // 6. Scale for Web
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        // Return raw PNG string
        return $canvas->toPng()->toString();
    }

    /**
     * V3 Logic: Since $image->mask() is undefined, we use
     * a composite approach to preserve color accuracy.
     */
    private function createTintedLayer(ImageInterface $mask, string $hex): ImageInterface
    {
        $width = $mask->width();
        $height = $mask->height();

        // Step A: Create the pure Hex color layer (PIXI 'tint' equivalent)
        $tint = Image::create($width, $height)->fill($hex);

        /**
         * Step B: Handling the Mask
         * In v3, we achieve masking by using the shirt texture as an overlay
         * with specific opacity. This preserves the Hex color in the midtones.
         */
        $texture = clone $mask;
        $texture->greyscale()->contrast(20);

        /**
         * Step C: The Accuracy Blend
         * We place the texture (shadows/folds) OVER the pure color.
         * Using 35% opacity ensures the shirt looks like fabric
         * without changing the actual Hex value of the mid-tones.
         */
        $tint->place($texture, 'top-left', 0, 0, 35);

        return $tint;
    }
}
