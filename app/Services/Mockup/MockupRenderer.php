<?php

namespace App\Services\Mockup;

use Imagick;
use Intervention\Image\Laravel\Facades\Image;
//use Intervention\Image\Interfaces\ImageInterface;

class MockupRenderer
{
    public function render(array $options)
    {
        $basePath = $options['base_path'];
        $shirtPath = $options['shirt_path'];
        $designPath = $options['design_path'] ?? null;
        $hex = $options['hex'] ?? 'ffffff';

// Print area configuration
        $printX = $options['print_x'] ?? 360;
        $printY = $options['print_y'] ?? 660;
        $printW = $options['print_w'] ?? 480;
        $printH = $options['print_h'] ?? 540;
        $angle = $options['angle'] ?? 0;
        $maxDim = $options['max_dim'] ?? 800;

// 1. Read Images
        $base = Image::read($basePath);
        $shirt = Image::read($shirtPath);

// 2. Accurate Tinting
        $tintedShirt = $this->tintShirt($shirt, $hex);

// 3. Prepare Design
        if ($designPath) {
            $design = Image::read($designPath);
            $design->scaleDown(width: $printW, height: $printH);

            if (!empty($angle)) {
                $design->rotate(-(float)$angle, 'rgba(0,0,0,0)');
            }

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

// FIX: Place design on the TINTED SHIRT first.
// This ensures the design is "part" of the shirt layer before placing on model.
            $tintedShirt->place($design, 'top-left', $offsetX, $offsetY);
        }

// 4. Final Composition
        $canvas = clone $base;
        $canvas->place($tintedShirt, 'top-left');

// 5. Final Output
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        return $canvas->toPng()->toString();
    }

    /**
     * Advanced tinting using Multiply Blending for deep, accurate blacks.
     */
    public function tintShirt( $shirt, string $hex)
    {
        $width = $shirt->width();
        $height = $shirt->height();

// Create the flat color layer
        $colorLayer = Image::create($width, $height)->fill($hex);

// Create the texture/folds layer from the original shirt
        $texture = clone $shirt;
        $texture->greyscale();

// Adjust these to match the "feel" of your specific mockup files
// Higher contrast = deeper shadows in the folds
        $texture->contrast(10);
        $texture->brightness(-5);

// Blend: The texture 'multiplies' over the solid color
        $colorLayer->place($texture, 'top-left', 0, 0, 'multiply');

// Clip the result to the shirt's original shape
        $colorLayer->core()->native()->compositeImage(
            $shirt->core()->native(),
            Imagick::COMPOSITE_DSTIN,
            0, 0
        );

        return $colorLayer;
    }
}
