<?php

namespace App\Services\Mockup;

use Imagick;
use ImagickPixel;

class MockupRenderer
{
    public function render(array $options): string
    {
        $basePath     = $options['base_path'] ?? null;
        $maskPath     = $options['shirt_mask_path'] ?? ($options['shirt_path'] ?? null);
        $shadowPath   = $options['shirt_shadow_path'] ?? ($options['shadow_path'] ?? null);
        $designPath   = $options['design_path'] ?? null;

        $hex          = $this->normalizeHex($options['hex'] ?? null);
        $maxDim       = (int) ($options['max_dim'] ?? 1600);
        $warp         = $options['warp_points'] ?? null;
        $skipMaskClip = (bool) ($options['skip_mask_clip'] ?? false);

        $renderMode = strtolower((string) ($options['render_mode'] ?? 'logo'));
        if (!in_array($renderMode, ['logo', 'full_art'], true)) {
            $renderMode = 'logo';
        }

        $designScale  = (float) ($options['design_scale'] ?? 0.95);
        $designScale  = max(0.05, min(4.0, $designScale));

        $textureStrength   = (float) ($options['texture_strength'] ?? 0.28);
        $highlightStrength = (float) ($options['highlight_strength'] ?? 0.06);
        $shadowStrength    = (float) ($options['shadow_strength'] ?? 1.03);
        $designOpacity     = (float) ($options['design_opacity'] ?? 0.96);
        $designSoftness    = (float) ($options['design_softness'] ?? 0.16);

        $textureStrength   = max(0.0, min(1.0, $textureStrength));
        $highlightStrength = max(0.0, min(1.0, $highlightStrength));
        $shadowStrength    = max(0.0, min(2.0, $shadowStrength));
        $designOpacity     = max(0.0, min(1.0, $designOpacity));
        $designSoftness    = max(0.0, min(2.0, $designSoftness));

        $displaceX         = (float) ($options['displace_x'] ?? 1.0);
        $displaceY         = (float) ($options['displace_y'] ?? 1.8);
        $displaceBlur      = (float) ($options['displace_blur'] ?? 1.9);
        $displaceEmboss    = (float) ($options['displace_emboss'] ?? 0.28);
        $displaceContrast  = (float) ($options['displace_contrast'] ?? 3.5);

        $displaceX         = max(0.0, min(40.0, $displaceX));
        $displaceY         = max(0.0, min(40.0, $displaceY));
        $displaceBlur      = max(0.0, min(10.0, $displaceBlur));
        $displaceEmboss    = max(0.1, min(10.0, $displaceEmboss));
        $displaceContrast  = max(0.0, min(100.0, $displaceContrast));

        if (!$basePath || !file_exists($basePath)) {
            throw new \InvalidArgumentException("Base image not found: {$basePath}");
        }

        if (!$maskPath || !file_exists($maskPath)) {
            throw new \InvalidArgumentException("Mask image not found: {$maskPath}");
        }

        if ($shadowPath && !file_exists($shadowPath)) {
            $shadowPath = null;
        }

        if ($designPath && !file_exists($designPath)) {
            throw new \InvalidArgumentException("Design image not found: {$designPath}");
        }

        $sourceBase = $this->load($basePath);
        $canvas     = $this->load($basePath);

        $w = $canvas->getImageWidth();
        $h = $canvas->getImageHeight();

        $mask   = $this->load($maskPath, $w, $h);
        $shadow = $shadowPath ? $this->load($shadowPath, $w, $h) : null;

        // recolor product while preserving selected hue
        if ($hex) {
            $tintedBase = $this->buildTintedProductPreserveHue($sourceBase, $mask, $hex);
            $canvas->compositeImage($tintedBase, Imagick::COMPOSITE_DEFAULT, 0, 0);
            $tintedBase->clear();
            $tintedBase->destroy();
        }

        if ($designPath) {
            $bounds = $this->alphaBounds($mask);

            $srcX = $bounds['x'];
            $srcY = $bounds['y'];
            $srcW = $bounds['w'];
            $srcH = $bounds['h'];

            $design = $this->load($designPath);

            $fitted = $this->fitContain($design, $srcW, $srcH);
            $design->clear();
            $design->destroy();
            $design = $fitted;

            if ($designScale != 1.0) {
                $scaled = $this->scaleImage($design, $designScale);
                $design->clear();
                $design->destroy();
                $design = $scaled;
            }

            $plate = new Imagick();
            $plate->newImage($srcW, $srcH, new ImagickPixel('transparent'), 'png');
            $plate->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

            $localX = (int) round(($srcW - $design->getImageWidth()) / 2);
            $localY = (int) round(($srcH - $design->getImageHeight()) / 2);

            $plate->compositeImage($design, Imagick::COMPOSITE_DEFAULT, $localX, $localY);

            $warped = new Imagick();
            $warped->newImage($w, $h, new ImagickPixel('transparent'), 'png');
            $warped->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $warped->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);

            if ($this->hasWarp($warp)) {
                $warped->compositeImage($plate, Imagick::COMPOSITE_DEFAULT, 0, 0);

                $points = [
                    0,     0,      $warp['tl']['x'] ?? $warp['tl'][0], $warp['tl']['y'] ?? $warp['tl'][1],
                    $srcW,  0,      $warp['tr']['x'] ?? $warp['tr'][0], $warp['tr']['y'] ?? $warp['tr'][1],
                    $srcW,  $srcH,  $warp['br']['x'] ?? $warp['br'][0], $warp['br']['y'] ?? $warp['br'][1],
                    0,      $srcH,  $warp['bl']['x'] ?? $warp['bl'][0], $warp['bl']['y'] ?? $warp['bl'][1],
                ];

                $warped->distortImage(Imagick::DISTORTION_PERSPECTIVE, $points, false);
            } else {
                $warped->compositeImage($plate, Imagick::COMPOSITE_DEFAULT, $srcX, $srcY);
            }

            if (!$skipMaskClip) {
                $clipped = $this->clipToMask($warped, $mask);
                $warped->clear();
                $warped->destroy();
                $warped = $clipped;
            }

            if ($designOpacity < 1.0) {
                $this->multiplyAlpha($warped, $designOpacity);
            }

            if ($designSoftness > 0) {
                $warped->gaussianBlurImage(0, $designSoftness);
            }

            if ($displaceX > 0 || $displaceY > 0) {
                $displaceMap = $this->buildDisplacementMap(
                    $canvas,
                    $mask,
                    $displaceBlur,
                    $displaceEmboss,
                    $displaceContrast
                );

                $displaced = $this->applyDisplacementMap(
                    $warped,
                    $displaceMap,
                    $displaceX,
                    $displaceY
                );

                $displaceMap->clear();
                $displaceMap->destroy();

                $warped->clear();
                $warped->destroy();
                $warped = $displaced;

                if (!$skipMaskClip) {
                    $reclipped = $this->clipToMask($warped, $mask);
                    $warped->clear();
                    $warped->destroy();
                    $warped = $reclipped;
                }
            }

            if ($renderMode === 'full_art') {
                $integrated = $this->buildFullArtColorSafePrint(
                    $warped,
                    $canvas,
                    $textureStrength,
                    $highlightStrength
                );
            } else {
                $integrated = $this->buildLogoIntegratedPrint(
                    $warped,
                    $canvas,
                    $textureStrength,
                    $highlightStrength
                );
            }

            $canvas->compositeImage($integrated, Imagick::COMPOSITE_DEFAULT, 0, 0);

            $integrated->clear();
            $integrated->destroy();

            $warped->clear();
            $warped->destroy();

            $plate->clear();
            $plate->destroy();

            $design->clear();
            $design->destroy();
        }

        if ($shadow) {
            $shadowBlend = clone $shadow;
            $this->multiplyAlpha($shadowBlend, 0.18 * $shadowStrength);
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

        return $blob;
    }

    private function hasWarp(?array $warp): bool
    {
        return is_array($warp)
            && isset($warp['tl'], $warp['tr'], $warp['br'], $warp['bl']);
    }

    private function alphaBounds(Imagick $mask): array
    {
        $tmp = clone $mask;
        $tmp->trimImage(0);
        $page = $tmp->getImagePage();

        $result = [
            'x' => max(0, (int) ($page['x'] ?? 0)),
            'y' => max(0, (int) ($page['y'] ?? 0)),
            'w' => max(1, $tmp->getImageWidth()),
            'h' => max(1, $tmp->getImageHeight()),
        ];

        $tmp->clear();
        $tmp->destroy();

        return $result;
    }

    private function clipToMask(Imagick $image, Imagick $mask): Imagick
    {
        $result = clone $image;

        $clip = clone $mask;
        $clip->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $result->compositeImage($clip, Imagick::COMPOSITE_DSTIN, 0, 0);

        $clip->clear();
        $clip->destroy();

        return $result;
    }

    private function buildTintedProductPreserveHue(Imagick $base, Imagick $mask, string $hex): Imagick
    {
        $w = $base->getImageWidth();
        $h = $base->getImageHeight();

        $luma = $this->hexLuma($hex);

        $shade = clone $base;
        $shade->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $shade->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $shade->modulateImage(100, 0, 100);

        if ($luma >= 235) {
            $shade->brightnessContrastImage(-10, 20);
            $shade->gammaImage(1.05);
        } elseif ($luma >= 200) {
            $shade->brightnessContrastImage(-4, 14);
            $shade->gammaImage(1.02);
        } else {
            $shade->brightnessContrastImage(2, 10);
            $shade->gammaImage(0.99);
        }

        $solid = new Imagick();
        $solid->newImage($w, $h, new ImagickPixel($hex), 'png');
        $solid->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $solid->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $solid->compositeImage($shade, Imagick::COMPOSITE_MULTIPLY, 0, 0);

        $detail = clone $base;
        $detail->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $detail->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

        $detailAlpha = $luma >= 235 ? 0.10 : 0.06;
        $this->multiplyAlpha($detail, $detailAlpha);

        $solid->compositeImage($detail, Imagick::COMPOSITE_SOFTLIGHT, 0, 0);

        $detail->clear();
        $detail->destroy();

        $shade->clear();
        $shade->destroy();

        return $solid;
    }

    private function buildDisplacementMap(
        Imagick $shirt,
        Imagick $mask,
        float $blur,
        float $embossRadius,
        float $contrast
    ): Imagick {
        $map = clone $shirt;
        $map->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $map->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $map->modulateImage(100, 0, 100);

        if ($blur > 0) {
            $map->gaussianBlurImage(0, $blur);
        }

        $map->embossImage(0, $embossRadius);

        if ($contrast > 0) {
            $map->brightnessContrastImage(0, $contrast);
        }

        $map->gaussianBlurImage(0, 0.35);

        return $map;
    }

    private function applyDisplacementMap(
        Imagick $print,
        Imagick $displaceMap,
        float $scaleX,
        float $scaleY
    ): Imagick {
        $result = clone $print;
        $result->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $result->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);

        try {
            if (\defined('Imagick::COMPOSITE_DISPLACE')) {
                $result->setImageArtifact('compose:args', sprintf('%.3fx%.3f', $scaleX, $scaleY));
                $result->compositeImage($displaceMap, Imagick::COMPOSITE_DISPLACE, 0, 0);
                $result->deleteImageArtifact('compose:args');
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return $result;
    }

    private function applyPrintAlphaToMap(Imagick $map, Imagick $printSource, float $alphaFactor): void
    {
        $alpha = clone $printSource;
        $alpha->separateImageChannel(Imagick::CHANNEL_ALPHA);

        $map->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $map->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $this->multiplyAlpha($map, $alphaFactor);

        $alpha->clear();
        $alpha->destroy();
    }

    private function buildDarkTransferMap(Imagick $shirt, Imagick $printSource, float $strength): Imagick
    {
        $map = clone $shirt;
        $map->modulateImage(100, 0, 100);
        $map->gaussianBlurImage(0, 0.9);
        $map->brightnessContrastImage(-16, 20);

        $this->applyPrintAlphaToMap($map, $printSource, 0.18 * $strength);

        return $map;
    }

    private function buildTextureSoftlightMap(Imagick $shirt, Imagick $printSource, float $strength): Imagick
    {
        $map = clone $shirt;
        $map->modulateImage(100, 0, 100);
        $map->gaussianBlurImage(0, 0.60);
        $map->brightnessContrastImage(0, 10);

        $this->applyPrintAlphaToMap($map, $printSource, 0.12 * $strength);

        return $map;
    }

    private function buildHighlightTransferMap(Imagick $shirt, Imagick $printSource, float $strength): Imagick
    {
        $map = clone $shirt;
        $map->modulateImage(100, 0, 100);
        $map->gaussianBlurImage(0, 1.1);
        $map->brightnessContrastImage(10, 18);
        $map->gammaImage(0.92);

        $this->applyPrintAlphaToMap($map, $printSource, 0.10 * $strength);

        return $map;
    }

    private function buildLogoIntegratedPrint(
        Imagick $warped,
        Imagick $shirt,
        float $textureStrength,
        float $highlightStrength
    ): Imagick {
        $result = clone $warped;

        $darkTransfer = $this->buildDarkTransferMap($shirt, $warped, $textureStrength);
        $result->compositeImage($darkTransfer, Imagick::COMPOSITE_MULTIPLY, 0, 0);
        $darkTransfer->clear();
        $darkTransfer->destroy();

        $textureMap = $this->buildTextureSoftlightMap($shirt, $warped, $textureStrength);
        $result->compositeImage($textureMap, Imagick::COMPOSITE_SOFTLIGHT, 0, 0);
        $textureMap->clear();
        $textureMap->destroy();

        $highlightMap = $this->buildHighlightTransferMap($shirt, $warped, $highlightStrength);
        $result->compositeImage($highlightMap, Imagick::COMPOSITE_SCREEN, 0, 0);
        $highlightMap->clear();
        $highlightMap->destroy();

        return $result;
    }

    private function buildFullArtColorSafePrint(
        Imagick $warped,
        Imagick $shirt,
        float $textureStrength,
        float $highlightStrength
    ): Imagick {
        $result = clone $warped;

        $alpha = clone $warped;
        $alpha->separateImageChannel(Imagick::CHANNEL_ALPHA);

        $darkMap = clone $shirt;
        $darkMap->modulateImage(100, 0, 100);
        $darkMap->gaussianBlurImage(0, 1.2);
        $darkMap->brightnessContrastImage(-10, 18);
        $darkMap->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $darkMap->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $this->multiplyAlpha($darkMap, 0.08 + (0.10 * $textureStrength));
        $result->compositeImage($darkMap, Imagick::COMPOSITE_MULTIPLY, 0, 0);

        $textureMap = clone $shirt;
        $textureMap->modulateImage(100, 0, 100);
        $textureMap->gaussianBlurImage(0, 0.9);
        $textureMap->brightnessContrastImage(0, 8);
        $textureMap->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $textureMap->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $this->multiplyAlpha($textureMap, 0.04 + (0.06 * $textureStrength));
        $result->compositeImage($textureMap, Imagick::COMPOSITE_SOFTLIGHT, 0, 0);

        $lightMap = clone $shirt;
        $lightMap->modulateImage(100, 0, 100);
        $lightMap->gaussianBlurImage(0, 1.4);
        $lightMap->brightnessContrastImage(8, 14);
        $lightMap->gammaImage(0.95);
        $lightMap->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $lightMap->compositeImage($alpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        $this->multiplyAlpha($lightMap, 0.03 + (0.06 * $highlightStrength));
        $result->compositeImage($lightMap, Imagick::COMPOSITE_SCREEN, 0, 0);

        $darkMap->clear();
        $darkMap->destroy();

        $textureMap->clear();
        $textureMap->destroy();

        $lightMap->clear();
        $lightMap->destroy();

        $alpha->clear();
        $alpha->destroy();

        return $result;
    }

    private function fitContain(Imagick $img, int $maxW, int $maxH): Imagick
    {
        $copy = clone $img;
        $copy->thumbnailImage($maxW, $maxH, true, true);
        return $copy;
    }

    private function scaleImage(Imagick $img, float $scale): Imagick
    {
        $copy = clone $img;

        $newW = max(1, (int) round($copy->getImageWidth() * $scale));
        $newH = max(1, (int) round($copy->getImageHeight() * $scale));

        $copy->resizeImage($newW, $newH, Imagick::FILTER_LANCZOS, 1);

        return $copy;
    }

    private function multiplyAlpha(Imagick $image, float $factor): void
    {
        $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $image->evaluateImage(
            Imagick::EVALUATE_MULTIPLY,
            $factor,
            Imagick::CHANNEL_ALPHA
        );
    }

    private function load(string $path, ?int $targetW = null, ?int $targetH = null): Imagick
    {
        $img = new Imagick($path);
        $img->setImageFormat('png');
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $img->setImageBackgroundColor(new ImagickPixel('transparent'));

        if ($targetW && $targetH) {
            if ($img->getImageWidth() !== $targetW || $img->getImageHeight() !== $targetH) {
                $img->resizeImage($targetW, $targetH, Imagick::FILTER_LANCZOS, 1);
            }
        }

        return $img;
    }

    private function hexLuma(string $hex): float
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0]
                . $hex[1] . $hex[1]
                . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
    }

    private function normalizeHex(?string $hex): ?string
    {
        if (!$hex) {
            return null;
        }

        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return '#' . strtolower($hex);
    }
}
