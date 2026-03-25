<?php

namespace App\Services\Mockup;

use Imagick;
use ImagickPixel;

class MockupRenderer
{
    public function render(array $options): string
    {
        $basePath   = $options['base_path'] ?? null;
        $maskPath   = $options['shirt_mask_path'] ?? ($options['shirt_path'] ?? null);
        $shadowPath = $options['shirt_shadow_path'] ?? ($options['shadow_path'] ?? null);
        $designPath = $options['design_path'] ?? null;
        $hex        = $this->normalizeHex($options['hex'] ?? null);

        $printX = (int)($options['print_x'] ?? 360);
        $printY = (int)($options['print_y'] ?? 660);
        $printW = max(1, (int)($options['print_w'] ?? 480));
        $printH = max(1, (int)($options['print_h'] ?? 540));
        $maxDim = (int)($options['max_dim'] ?? 800);
        $angle  = (float)($options['angle'] ?? 0);

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

        // sourceBase = الأصل الذي نستخرج منه texture/folds
        $sourceBase = $this->load($basePath);
        $canvas     = $this->load($basePath);

        $w = $canvas->getImageWidth();
        $h = $canvas->getImageHeight();

        $mask   = $this->load($maskPath, $w, $h);
        $shadow = $shadowPath ? $this->load($shadowPath, $w, $h) : null;

        // 1) recolor shirt
        if ($hex) {
            $tintedShirt = $this->buildTintedShirtFromBase($sourceBase, $mask, $hex);
            $canvas->compositeImage($tintedShirt, Imagick::COMPOSITE_DEFAULT, 0, 0);
            $tintedShirt->clear();
            $tintedShirt->destroy();
        }

        // 2) design layer with professional fabric blending
        if ($designPath) {
            $design = $this->load($designPath);

            if (abs($angle) > 0.001) {
                $design->setImageBackgroundColor(new ImagickPixel('transparent'));
                $design->rotateImage(new ImagickPixel('transparent'), -$angle);
            }

            $design = $this->fitContain($design, $printW, $printH);

            $offsetX = $printX + (int)(($printW - $design->getImageWidth()) / 2);
            $offsetY = $printY + (int)(($printH - $design->getImageHeight()) / 2);

            $designLayer = new Imagick();
            $designLayer->newImage($w, $h, new ImagickPixel('transparent'), 'png');
            $designLayer->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
            $designLayer->compositeImage($design, Imagick::COMPOSITE_DEFAULT, $offsetX, $offsetY);

            // قص التصميم داخل التيشيرت
            $shirtMaskForClip = clone $mask;
            $shirtMaskForClip->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $designLayer->compositeImage($shirtMaskForClip, Imagick::COMPOSITE_DSTIN, 0, 0);

            // احتفظ بألفا التصميم نفسه بعد القص
            $designAlpha = clone $designLayer;

            // Apply fabric texture/folds to design itself
            $this->applyFabricToDesignLayer($designLayer, $sourceBase, $mask, $shadow);

            // ارجع ألفا التصميم نفسه حتى لا يملأ layer بالكامل
            $designLayer->compositeImage($designAlpha, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

            $canvas->compositeImage($designLayer, Imagick::COMPOSITE_DEFAULT, 0, 0);

            $designAlpha->clear();
            $designAlpha->destroy();

            $shirtMaskForClip->clear();
            $shirtMaskForClip->destroy();

            $designLayer->clear();
            $designLayer->destroy();

            $design->clear();
            $design->destroy();
        }

        // 3) global shadow on top, but gently
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

        return $blob;
    }

    private function applyFabricToDesignLayer(
        Imagick $designLayer,
        Imagick $sourceBase,
        Imagick $mask,
        ?Imagick $shadow
    ): void {
        // grayscale fabric map from original shirt
        $fabric = $this->buildMaskedGrayscaleMap($sourceBase, $mask);

        // 1) soft light overall fabric grain/folds
        $soft = clone $fabric;
        $this->multiplyAlpha($soft, 0.22);
        $designLayer->compositeImage($soft, Imagick::COMPOSITE_SOFTLIGHT, 0, 0);

        // 2) dark folds only
        $dark = clone $fabric;
        $dark->gammaImage(1.28);
        $this->multiplyAlpha($dark, 0.14);
        $designLayer->compositeImage($dark, Imagick::COMPOSITE_MULTIPLY, 0, 0);

        // 3) highlights only
        $light = clone $fabric;
        $light->gammaImage(0.82);
        $this->multiplyAlpha($light, 0.07);
        $designLayer->compositeImage($light, Imagick::COMPOSITE_SCREEN, 0, 0);

        // 4) local shadow on design itself
        if ($shadow) {
            $localShadow = clone $shadow;
            $this->multiplyAlpha($localShadow, 0.12);
            $designLayer->compositeImage($localShadow, Imagick::COMPOSITE_MULTIPLY, 0, 0);
            $localShadow->clear();
            $localShadow->destroy();
        }

        $soft->clear();
        $soft->destroy();

        $dark->clear();
        $dark->destroy();

        $light->clear();
        $light->destroy();

        $fabric->clear();
        $fabric->destroy();
    }

    private function buildMaskedGrayscaleMap(Imagick $base, Imagick $mask): Imagick
    {
        $map = clone $base;
        $map->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $map->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

        // remove shirt color, keep luminance/folds
        $map->modulateImage(100, 0, 100);

        // small blur so it behaves like fabric shading, not harsh edges
        $map->gaussianBlurImage(1.2, 0.6);

        return $map;
    }

    private function buildTintedShirtFromBase(Imagick $base, Imagick $mask, string $hex): Imagick
    {
        $w = $base->getImageWidth();
        $h = $base->getImageHeight();

        $texture = clone $base;
        $texture->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $texture->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

        // keep folds/highlights only
        $texture->modulateImage(100, 0, 100);
        $texture->gammaImage(0.92);

        $solid = new Imagick();
        $solid->newImage($w, $h, new ImagickPixel($hex), 'png');
        $solid->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $solid->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);

        // fabric folds on shirt color
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
