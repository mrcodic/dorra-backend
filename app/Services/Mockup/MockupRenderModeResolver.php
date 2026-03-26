<?php

namespace App\Services\Mockup;

class MockupRenderModeResolver
{
    public function resolve(array $context): string
    {
        $coverageRatio = (float)($context['coverage_ratio'] ?? 0);
        $placedWidthRatio = (float)($context['placed_width_ratio'] ?? 0);
        $placedHeightRatio = (float)($context['placed_height_ratio'] ?? 0);
        $hasAlpha = (bool)($context['has_alpha'] ?? true);
        $mime = strtolower((string)($context['mime'] ?? ''));

        if (
            $coverageRatio >= 0.35 ||
            $placedWidthRatio >= 0.65 ||
            $placedHeightRatio >= 0.65 ||
            ((!$hasAlpha) && in_array($mime, ['image/jpeg', 'image/jpg'], true))
        ) {
            return 'full_art';
        }

        return 'logo';
    }
}
