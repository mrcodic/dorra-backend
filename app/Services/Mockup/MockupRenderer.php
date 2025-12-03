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
        $basePath   = $options['base_path'];
        $shirtPath  = $options['shirt_path'];
        $designPath = $options['design_path'] ?? null;
        $hex        = $options['hex'] ?? null;

        $printX     = $options['print_x'] ?? 360;
        $printY     = $options['print_y'] ?? 660;
        $printW     = $options['print_w'] ?? 480;
        $printH     = $options['print_h'] ?? 540;

        $maxDim     = $options['max_dim'] ?? 800;

        $base  = Image::read($basePath);
        $shirt = Image::read($shirtPath);
        $design = $designPath ? Image::read($designPath) : null;

        if (!empty($hex)) {
            $shirt = $this->tintShirt($shirt, $hex);
        }

        // Resize shirt to base size
        $shirt->resize($base->width(), $base->height());

        $canvas = clone $base;
        $canvas->place($shirt);

        if ($design) {
            $ratio = min($printW / $design->width(), $printH / $design->height());
            $design->resize((int)($design->width() * $ratio), (int)($design->height() * $ratio));

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY + (int)(($printH - $design->height()) / 2);

            $canvas->place($design, offset_x: $offsetX, offset_y: $offsetY);
        }

        // Scale down for web
        if ($maxDim > 0) {
            $ratio = min($maxDim / $canvas->width(), $maxDim / $canvas->height());
            if ($ratio < 1) {
                $canvas->resize((int)($canvas->width() * $ratio), (int)($canvas->height() * $ratio));
            }
        }

        // Return PNG string (version 3.x compatible)
        return $canvas->toPng()->toString();
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
