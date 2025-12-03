<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;
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
    public function render(array $options)
    {
        // ----- 1) Read options with sane defaults -----
        $basePath   = $options['base_path'];
        $shirtPath  = $options['shirt_path'];
        $designPath = $options['design_path'] ?? null;
        $hex        = $options['hex'] ?? null;

        $printX     = $options['print_x'] ?? 360;
        $printY     = $options['print_y'] ?? 660;
        $printW     = $options['print_w'] ?? 480;
        $printH     = $options['print_h'] ?? 540;

        $maxDim     = $options['max_dim'] ?? 800;

        // ----- 2) Read images -----
        $base  = Image::make($basePath);
        $shirt = Image::make($shirtPath);

        $design = $designPath ? Image::make($designPath) : null;

        // ----- 3) Tint the shirt (only if hex provided) -----
        $tintedShirt = $shirt;
        if (!empty($hex)) {
            $tintedShirt = $this->tintShirt($shirt, $hex);
        }

        // ----- 4) Resize shirt to match base -----
        $tintedShirt->resize($base->width(), $base->height());

        // ----- 5) Compose canvas -----
        $canvas = clone $base;

        // place shirt on base
        $canvas->insert($tintedShirt);

        // ----- 6) Place design if exists -----
        if ($design) {
            // scale design to fit in print box (maintain aspect ratio)
            $ratio = min($printW / $design->width(), $printH / $design->height());
            $design->resize(
                (int)($design->width() * $ratio),
                (int)($design->height() * $ratio)
            );

            // center design inside print box
            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY + (int)(($printH - $design->height()) / 2);

            $canvas->insert($design, 'top-left', $offsetX, $offsetY);
        }

        // ----- 7) Scale down for web -----
        if ($maxDim > 0) {
            $ratio = min($maxDim / $canvas->width(), $maxDim / $canvas->height());
            if ($ratio < 1) { // only scale down
                $canvas->resize(
                    (int)($canvas->width() * $ratio),
                    (int)($canvas->height() * $ratio)
                );
            }
        }

        // ----- 8) Return encoded PNG -----
        return $canvas->encode('png')->__toString();
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
