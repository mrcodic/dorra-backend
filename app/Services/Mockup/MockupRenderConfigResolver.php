<?php

namespace App\Services\Mockup;

use App\Models\Mockup;

class MockupRenderConfigResolver
{
    public function resolve(
        Mockup $mockup,
        string $side        = 'front',
        string $renderMode  = 'logo',
        array  $overrides   = []
    ): array {
        $renderMode = $this->normalizeMode($renderMode);

        $sideSetting = $mockup->sideSettings
            ->firstWhere('side', strtolower($side));

        // قراءة الـ preset من ملف الإعدادات
        $preset = config("mockup.presets.{$renderMode}",
            config('mockup.presets.logo'));

        // override من الداتابيز لو موجود
        $dbPresets      = is_array($sideSetting?->render_presets) ? $sideSetting->render_presets : [];
        $dbPresetForMode = is_array($dbPresets[$renderMode] ?? null) ? $dbPresets[$renderMode] : [];

        return [
            'render_mode' => $renderMode,
            'warp_points' => $sideSetting?->warp_points,
            'preset'      => array_merge($preset, $dbPresetForMode, $overrides),
        ];
    }

    private function normalizeMode(string $mode): string
    {
        $mode = strtolower($mode);
        return in_array($mode, ['logo', 'full_art'], true) ? $mode : 'logo';
    }
}
