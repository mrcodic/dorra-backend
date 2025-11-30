<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

class MockupRenderer
{
    /**
     * Render a mockup with:
     * - base image (model wearing shirt)
     * - shirt PNG (transparent)
     * - optional design to place on shirt
     * - tint color (HEX)
     * - configurable print box & size
     */
    public function render(array $options): EncodedImageInterface
    {
        // ----- 1) Read options with sane defaults -----
        $basePath   = $options['base_path'];         // required
        $shirtPath  = $options['shirt_path'];        // required
        $designPath = $options['design_path'] ?? null;
dd($options);
        $hex        = $options['hex'] ?? '#D0293B';

        $printX     = $options['print_x'] ?? 360;
        $printY     = $options['print_y'] ?? 660;
        $printW     = $options['print_w'] ?? 480;
        $printH     = $options['print_h'] ?? 540;

        $maxDim     = $options['max_dim'] ?? 800;

        // ----- 2) Read images -----
        $base  = Image::read($basePath);
        $shirt = Image::read($shirtPath);

        $design = null;
        if ($designPath) {
            $design = Image::read($designPath);
        }

        // ----- 3) Tint the shirt -----
        $tintedShirt = $this->tintShirt($shirt, $hex);

        // ----- 4) Compose canvas -----
        $canvas = clone $base;

        // place shirt on base
        $canvas->place($tintedShirt, 'top-left');

        // ----- 5) Place design if exists -----
        if ($design) {
            // scale design to fit in print box
            $design->scaleDown(width: $printW, height: $printH);

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
        }

        // ----- 6) Scale down for web -----
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        // ----- 7) Return encoded PNG -----
        return $canvas->toPng();
    }

    /**
     * Tint shirt PNG with HEX color, preserving folds / texture.
     */
    public function tintShirt(ImageInterface $shirt, string $hex): ImageInterface
    {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Map 0..255 -> -40..40 for subtle colorize
        $map = function (int $c): int {
            return (int) round((($c - 128) / 127) * 40);
        };

        $rAdj = $map($r);
        $gAdj = $map($g);
        $bAdj = $map($b);

        $img = clone $shirt;

        $img->greyscale()
            ->colorize($rAdj, $gAdj, $bAdj)
            ->contrast(12)
            ->brightness(-18);

        return $img;
    }
}
