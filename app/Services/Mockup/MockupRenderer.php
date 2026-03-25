<?php

namespace App\Services\Mockup;

use Intervention\Image\Laravel\Facades\Image;

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

        // ----- 3) Tint the shirt (reads fresh from disk — no clone) -----
        $tintedShirt = (!empty($hex))
            ? $this->tintShirt($shirtPath, $hex)
            : Image::read($shirtPath);

        // ----- 4) Compose canvas: fresh base read + tinted shirt -----
        $canvas = Image::read($basePath);
        $canvas->place($tintedShirt, 'top-left', 0, 0);
        unset($tintedShirt); // free GD resource immediately

        // ----- 5) Place design if provided -----
        if ($designPath) {
            if (!file_exists($designPath)) {
                throw new \InvalidArgumentException("Design image not found: {$designPath}");
            }

            $design = Image::read($designPath);

            // Scale design to fit inside the print box
            $design->scaleDown(width: $printW, height: $printH);

            // Rotate around center, keeping alpha channel
            if (!empty($angle)) {
                $design = $design->rotate(-(float)$angle, 'transparent');
            }

            // Center the design horizontally within the print box
            $offsetX = $printX + (int)(($printW - $design->width()) / 2);
            $offsetY = $printY;

            $canvas->place($design, 'top-left', $offsetX, $offsetY);
            unset($design); // free GD resource immediately
        }

        // ----- 6) Scale down for web -----
        if ($maxDim > 0) {
            $canvas->scaleDown(width: $maxDim, height: $maxDim);
        }

        // ----- 7) Return encoded PNG -----
        return $canvas->toPng()->toString();
    }

    /**
     * Tint shirt PNG with HEX color, preserving fabric texture/folds.
     *
     * Reads fresh from disk instead of cloning to avoid GD memory duplication.
     *
     * Handles edge cases:
     *  - Pure black (#000000): greyscale + heavy darken, no colorize (colorize on black = still black)
     *  - Pure white (#ffffff): greyscale + strong brighten, slight contrast
     *  - Near-black / near-white: blended approach
     *  - All other colors: greyscale + colorize with wider range for vivid results
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
        $img->greyscale();

        // ── Pure / near-black (brightness < 20) ──────────────────────────
        // colorize() has no effect on pure black pixels, so just darken hard.
        if ($brightness < 20) {
            $img->brightness(-55)
                ->contrast(15);
            return $img;
        }

        // ── Pure / near-white (brightness > 235) ─────────────────────────
        // Brighten strongly so it actually looks white, not grey.
        if ($brightness > 235) {
            $img->brightness(55)
                ->contrast(-10);
            return $img;
        }

        // ── All other colors ──────────────────────────────────────────────
        // Map 0..255 → -100..100 for a much more vivid colorize range.
        // The original code used -40..40 which was too subtle and broke at extremes.
        $map = function (int $c): int {
            return (int)round((($c - 128) / 128) * 100);
        };

        $rAdj = $map($r);
        $gAdj = $map($g);
        $bAdj = $map($b);

        // Shift overall brightness closer to the target color's luminance
        // so dark colors look dark and light colors look light on the shirt.
        $brightnessShift = (int)(($brightness - 128) / 128 * 30);

        $img->colorize($rAdj, $gAdj, $bAdj)
            ->contrast(10)
            ->brightness($brightnessShift);

        return $img;
    }
}
