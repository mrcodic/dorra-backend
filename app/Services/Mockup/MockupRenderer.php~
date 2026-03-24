<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;

class MockupRenderer
{
    public function render(array $options): string
    {
        // Increase memory limit for large images
        ini_set('memory_limit', '512M');

        $basePath   = $options['base_path'];
        $shirtPath  = $options['shirt_path'];
        $designPath = $options['design_path'] ?? null;
        $hex        = $options['hex'] ?? null;

        $printX = (int)($options['print_x'] ?? 360);
        $printY = (int)($options['print_y'] ?? 660);
        $printW = max(1, (int)($options['print_w'] ?? 480));
        $printH = max(1, (int)($options['print_h'] ?? 540));
        $maxDim = (int)($options['max_dim'] ?? 800);
        $angle  = (float)($options['angle'] ?? 0);

        if (!file_exists($basePath)) {
            throw new \InvalidArgumentException("Base image not found: {$basePath}");
        }
        if (!file_exists($shirtPath)) {
            throw new \InvalidArgumentException("Shirt/mask image not found: {$shirtPath}");
        }

        $shirt = Image::read($shirtPath);

        $tintedShirt = (!empty($hex))
            ? $this->tintShirt($shirt, $hex)
            : $shirt;

        // Free original shirt if tinting created a new object
        if ($tintedShirt !== $shirt) {
            unset($shirt);
        }

        $canvas = Image::read($basePath);
        $canvas->place($tintedShirt, 'top-left', 0, 0);

        // Free tinted shirt after placing
        unset($tintedShirt);
        gc_collect_cycles();

        if ($designPath) {
            if (!file_exists($designPath)) {
                throw new \InvalidArgumentException("Design image not found: {$designPath}");
            }

            $design = Image::read($designPath);

            if (!empty($angle)) {
                $design = $design->rotate(-(float)$angle, 'transparent');
            }

            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY + (int)(($printH - $design->height()) / 2);

            $canvas->place($design, 'top-left', $offsetX, $offsetY);

            // Free design after placing
            unset($design);
            gc_collect_cycles();
        }

        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        $result = $canvas->toPng()->toString();
        unset($canvas);
        gc_collect_cycles();

        return $result;
    }

    public function tintShirt($shirt, string $hex): mixed
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $brightness = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);

        // Avoid clone — read a fresh copy instead to prevent memory spike
        $img = Image::read($shirt->toJpeg(90)->toString());
        $img->greyscale();

        if ($brightness < 20) {
            $img->brightness(-55)->contrast(15);
            return $img;
        }

        if ($brightness > 235) {
            $img->brightness(55)->contrast(-10);
            return $img;
        }

        $map = fn(int $c): int => (int)round((($c - 128) / 128) * 100);

        $img->colorize($map($r), $map($g), $map($b))
            ->contrast(10)
            ->brightness((int)(($brightness - 128) / 128 * 30));

        return $img;
    }
}
