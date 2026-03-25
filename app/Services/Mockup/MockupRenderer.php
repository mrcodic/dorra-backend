<?php

namespace App\Services\Mockup;

use Imagick;
use ImagickPixel;

class MockupRenderer
{
    public function render(array $options): string
    {
        $basePath      = $options['base_path'] ?? null;
        $maskPath      = $options['shirt_mask_path'] ?? ($options['shirt_path'] ?? null);
        $shadowPath    = $options['shirt_shadow_path'] ?? ($options['shadow_path'] ?? null);
        $printMaskPath = $options['print_mask_path'] ?? null;
        $designPath    = $options['design_path'] ?? null;
        $hex           = $this->normalizeHex($options['hex'] ?? null);

        $printX = (int)($options['print_x'] ?? 360);
        $printY = (int)($options['print_y'] ?? 660);
        $printW = max(1, (int)($options['print_w'] ?? 480));
        $printH = max(1, (int)($options['print_h'] ?? 540));
        $maxDim = (int)($options['max_dim'] ?? 1600);

        $warp = $options['warp_points'] ?? null;

        if (!$basePath || !file_exists($basePath)) {
            throw new \InvalidArgumentException("Base image not found: {$basePath}");
        }

        if (!$maskPath || !file_exists($maskPath)) {
            throw new \InvalidArgumentException("Mask image not found: {$maskPath}");
        }

        if ($shadowPath && !file_exists($shadowPath)) {
            $shadowPath = null;
        }

        if ($printMaskPath && !file_exists($printMaskPath)) {
            $printMaskPath = null;
        }

        if ($designPath && !file_exists($designPath)) {
            throw new \InvalidArgumentException("Design image not found: {$designPath}");
        }

        $sourceBase = $this->load($basePath);
        $canvas     = $this->load($basePath);

        $w = $canvas->getImageWidth();
        $h = $canvas->getImageHeight();

        $mask      = $this->load($maskPath, $w, $h);
        $shadow    = $shadowPath ? $this->load($shadowPath, $w, $h) : null;
        $printMask = $printMaskPath ? $this->load($printMaskPath, $w, $h) : null;

        // 1) recolor shirt
        if ($hex) {
            $tintedShirt = $this->buildTintedShirtFromBase($sourceBase, $mask, $hex);
            $canvas->compositeImage($tintedShirt, Imagick::COMPOSITE_DEFAULT, 0, 0);
            $tintedShirt->clear();
            $tintedShirt->destroy();
        }

        // 2) design with perspective warp
        if ($designPath) {
            $design = $this->load($designPath);

            [$srcX, $srcY, $srcW, $srcH] = $this->resolveSourceRect(
                $printMask,
                $warp,
                $printX,
                $printY,
                $printW,
                $printH
            );

            $design = $this->fitContain($design, $srcW, $srcH);

            $layer = new Imagick();
            $layer->newImage($w, $h, new ImagickPixel('transparent'), 'png');
            $layer->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

            $placeX = $srcX + (int)(($srcW - $design->getImageWidth()) / 2);
            $placeY = $srcY + (int)(($srcH - $design->getImageHeight()) / 2);

            $layer->compositeImage($design, Imagick::COMPOSITE_DEFAULT, $placeX, $placeY);

            if ($this->hasWarp($warp)) {
                $layer->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
                $points = [
                    $srcX,         $srcY,         $warp['tl'][0], $warp['tl'][1],
                    $srcX + $srcW, $srcY,         $warp['tr'][0], $warp['tr'][1],
                    $srcX + $srcW, $srcY + $srcH, $warp['br'][0], $warp['br'][1],
                    $srcX,         $srcY + $srcH, $warp['bl'][0], $warp['bl'][1],
                ];
                $layer->distortImage(Imagick::DISTORTION_PERSPECTIVE, $points, false);
            }

            // clip to print area first
            if ($printMask) {
                $clip = clone $printMask;
                $clip->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
                $layer->compositeImage($clip, Imagick::COMPOSITE_DSTIN, 0, 0);
                $clip->clear();
                $clip->destroy();
            }

            // do not let it escape the shirt
            $shirtClip = clone $mask;
            $shirtClip->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $layer->compositeImage($shirtClip, Imagick::COMPOSITE_DSTIN, 0, 0);
            $shirtClip->clear();
            $shirtClip->destroy();

            // local folds on design (light touch)
            $fabric = $this->buildMaskedGrayscaleMap($sourceBase, $mask);
            $fabricSoft = clone $fabric;
            $this->multiplyAlpha($fabricSoft, 0.14);
            $layer->compositeImage($fabricSoft, Imagick::COMPOSITE_SOFTLIGHT, 0, 0);

            $fabricDark = clone $fabric;
            $fabricDark->gammaImage(1.22);
            $this->multiplyAlpha($fabricDark, 0.09);
            $layer->compositeImage($fabricDark, Imagick::COMPOSITE_MULTIPLY, 0, 0);

            $fabricLight = clone $fabric;
            $fabricLight->gammaImage(0.86);
            $this->multiplyAlpha($fabricLight, 0.05);
            $layer->compositeImage($fabricLight, Imagick::COMPOSITE_SCREEN, 0, 0);

            $canvas->compositeImage($layer, Imagick::COMPOSITE_DEFAULT, 0, 0);

            $fabricSoft->clear();
            $fabricSoft->destroy();
            $fabricDark->clear();
            $fabricDark->destroy();
            $fabricLight->clear();
            $fabricLight->destroy();
            $fabric->clear();
            $fabric->destroy();

            $layer->clear();
            $layer->destroy();
            $design->clear();
            $design->destroy();
        }

        // 3) global shadow on top
        if ($shadow) {
            $shadowBlend = clone $shadow;
            $this->multiplyAlpha($shadowBlend, 0.18);
            $canvas->compositeImage($shadowBlend, Imagick::COMPOSITE_MULTIPLY, 0, 0);

            $shadowBlend->clear();
            $shadowBlend->destroy();

            $shadow->clear();
            $shadow->destroy();
        }

        if ($maxDim > 0) {
            $canvas->thumbnailImage($maxDim, $maxDim, true, true);
        }

        $canvas->setImageFormat('png');
        $blob = $canvas->getImageBlob();

        $canvas->clear();
        $canvas->destroy();

        $sourceBase->clear();
        $sourceBase->destroy();

        $mask->clear();
        $mask->destroy();

        if ($printMask) {
            $printMask->clear();
            $printMask->destroy();
        }

        return $blob;
    }

    private function resolveSourceRect(?Imagick $printMask, ?array $warp, int $printX, int $printY, int $printW, int $printH): array
    {
        if ($printMask) {
            $b = $this->alphaBounds($printMask);
            return [$b['x'], $b['y'], $b['w'], $b['h']];
        }

        if ($this->hasWarp($warp)) {
            $xs = [$warp['tl'][0], $warp['tr'][0], $warp['br'][0], $warp['bl'][0]];
            $ys = [$warp['tl'][1], $warp['tr'][1], $warp['br'][1], $warp['bl'][1]];
            $minX = (int) floor(min($xs));
            $minY = (int) floor(min($ys));
            $maxX = (int) ceil(max($xs));
            $maxY = (int) ceil(max($ys));
            return [$minX, $minY, max(1, $maxX - $minX), max(1, $maxY - $minY)];
        }

        return [$printX, $printY, $printW, $printH];
    }

    private function hasWarp(?array $warp): bool
    {
        return is_array($warp)
            && isset($warp['tl'], $warp['tr'], $warp['br'], $warp['bl'])
            && count($warp['tl']) === 2
            && count($warp['tr']) === 2
            && count($warp['br']) === 2
            && count($warp['bl']) === 2;
    }

    private function alphaBounds(Imagick $mask): array
    {
        $tmp = clone $mask;
        $tmp->trimImage(0);
        $page = $tmp->getImagePage();

        $result = [
            'x' => max(0, (int)($page['x'] ?? 0)),
            'y' => max(0, (int)($page['y'] ?? 0)),
            'w' => max(1, $tmp->getImageWidth()),
            'h' => max(1, $tmp->getImageHeight()),
        ];

        $tmp->clear();
        $tmp->destroy();

        return $result;
    }

    private function buildMaskedGrayscaleMap(Imagick $base, Imagick $mask): Imagick
    {
        $map = clone $base;
        $map->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $map->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $map->modulateImage(100, 0, 100);
        $map->gaussianBlurImage(1.0, 0.6);
        return $map;
    }

    private function buildTintedShirtFromBase(Imagick $base, Imagick $mask, string $hex): Imagick
    {
        $w = $base->getImageWidth();
        $h = $base->getImageHeight();

        $texture = clone $base;
        $texture->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $texture->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $texture->modulateImage(100, 0, 100);
        $texture->gammaImage(0.92);

        $solid = new Imagick();
        $solid->newImage($w, $h, new ImagickPixel($hex), 'png');
        $solid->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $solid->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $solid->compositeImage($texture, Imagick::COMPOSITE_MULTIPLY, 0, 0);

        $texture->clear();
        $texture->destroy();

        return $solid;
    }

    private function fitContain(Imagick $img, int $maxW, int $maxH): Imagick
    {
        $copy = clone $img;
        $copy->thumbnailImage($maxW, $maxH, true, true);
        return $copy;
    }

    private function multiplyAlpha(Imagick $image, float $factor): void
    {
        $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $image->evaluateImage(Imagick::EVALUATE_MULTIPLY, $factor, Imagick::CHANNEL_ALPHA);
    }

    private function load(string $path, ?int $targetW = null, ?int $targetH = null): Imagick
    {
        $img = new Imagick($path);
        $img->setImageFormat('png');
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        if ($targetW && $targetH) {
            if ($img->getImageWidth() !== $targetW || $img->getImageHeight() !== $targetH) {
                $img->resizeImage($targetW, $targetH, Imagick::FILTER_LANCZOS, 1);
            }
        }

        return $img;
    }

    private function normalizeHex(?string $hex): ?string
    {
        if (!$hex) {
            return null;
        }

        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return '#' . strtolower($hex);
    }
}
