<?php

namespace App\Traits;

use App\Services\Mockup\MockupRenderConfigResolver;
use App\Services\Mockup\MockupRenderer;
use App\Services\Mockup\MockupRenderModeResolver;
use Illuminate\Support\Facades\Log;

trait RendersTemplateMockups
{
    private function renderMockupsForTemplates(iterable $templates, string $collection): void
    {
        foreach ($templates as $template) {
            $this->renderMockups($template, $collection);
        }
    }

    private function renderMockups($template, string $collection): void
    {
        $template = $template->fresh(['mockups.types', 'mockups.media', 'mockups.sideSettings']);

        $mockups = $template->mockups;

        // ---- Cleanup ----
        $activeSides = collect();

        if ($mockups && $mockups->isNotEmpty()) {
            foreach ($mockups as $mockup) {
                foreach ($mockup->types as $type) {
                    $side = strtolower($type->value->name);
                    $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';

                    if ($collection === $expectedCollection) {
                        $activeSides->push($side);
                    }
                }
            }
        }

        $activeCategoryIds = $mockups?->pluck('category_id')->map(fn($id) => (int)$id)->toArray() ?? [];

        $template->getMedia('rendered_mockups')
            ->filter(function ($media) use ($activeSides, $activeCategoryIds, $collection) {
                $side = $media->getCustomProperty('side');
                $categoryId = (int)$media->getCustomProperty('category_id');
                $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';

                if ($expectedCollection !== $collection) {
                    return false;
                }

                return !$activeSides->contains($side)
                    || !in_array($categoryId, $activeCategoryIds, true);
            })
            ->each
            ->delete();

        if (!$mockups || $mockups->isEmpty()) {
            return;
        }

        // ===== خدمات الرندر =====
        $configResolver = app(MockupRenderConfigResolver::class);
        $modeResolver = app(MockupRenderModeResolver::class);

        foreach ($mockups as $mockup) {
            $positions = $mockup->pivot->positions ?? [];

            if (is_string($positions)) {
                $positions = json_decode($positions, true) ?? [];
            }

            foreach ($mockup->types as $type) {
                $side = strtolower($type->value->name);
                $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';

                if ($collection !== $expectedCollection) {
                    continue;
                }

                $base = $mockup->getMedia('mockups')
                    ->first(fn($m) => $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'base'
                    );

                $mask = $mockup->getMedia('mockups')
                    ->first(fn($m) => $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'mask'
                    );

                $shadow = $mockup->getMedia('mockups')
                    ->first(fn($m) => $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'shadow'
                    );

                if (!$base || !$mask) {
                    continue;
                }

                $design = $template->getFirstMedia($collection);

                if (!$design || !file_exists($design->getPath())) {
                    continue;
                }

                if (!$this->isValidImage($design->getPath())) {
                    Log::warning("Design file invalid/truncated: {$design->getPath()}");
                    continue;
                }

                // ===== جيب mask bounds =====
                try {
                    $maskImg = new \Imagick($mask->getPath());
                    $tmp = clone $maskImg;
                    $tmp->trimImage(0);
                    $page = $tmp->getImagePage();

                    $maskBounds = [
                        'x' => max(0, (int)($page['x'] ?? 0)),
                        'y' => max(0, (int)($page['y'] ?? 0)),
                        'w' => max(1, $tmp->getImageWidth()),
                        'h' => max(1, $tmp->getImageHeight()),
                    ];

                    $tmp->clear();
                    $tmp->destroy();
                    $maskImg->clear();
                    $maskImg->destroy();
                } catch (\Throwable $e) {
                    Log::error("Failed to read mask bounds for mockup {$mockup->id} side {$side}: " . $e->getMessage());
                    continue;
                }

                // ===== احسب design context =====
                try {
                    $designImg = new \Imagick($design->getPath());
                    $designW = $designImg->getImageWidth();
                    $designH = $designImg->getImageHeight();
                    $hasAlpha = $designImg->getImageAlphaChannel() !== 0;
                    $designImg->clear();
                    $designImg->destroy();
                } catch (\Throwable $e) {
                    Log::error("Failed to read design for mockup {$mockup->id}: " . $e->getMessage());
                    continue;
                }

                $designMime = $design->mime_type ?? 'image/png';
                $placedWidthRatio = $designW / max(1, $maskBounds['w']);
                $placedHeightRatio = $designH / max(1, $maskBounds['h']);
                $coverageRatio = ($placedWidthRatio + $placedHeightRatio) / 2;

                // ===== حدد الوضع تلقائياً =====
                $autoRenderMode = $modeResolver->resolve([
                    'coverage_ratio' => $coverageRatio,
                    'placed_width_ratio' => $placedWidthRatio,
                    'placed_height_ratio' => $placedHeightRatio,
                    'has_alpha' => $hasAlpha,
                    'mime' => $designMime,
                ]);

                // ===== جيب الإعدادات والـ warp =====
                $config = $configResolver->resolve(
                    $mockup,
                    $side,
                    $autoRenderMode,
                );

                $renderMode = $config['render_mode'];
                $preset = $config['preset'];
                $warp = $config['warp_points'];
dd([
    'base_path' => $base->getPath(),
    'shirt_mask_path' => $mask->getPath(),
    'shirt_shadow_path' => $shadow?->getPath(),
    'design_path' => $design->getPath(),
    'warp_points' => $warp,
    'render_mode' => $renderMode,
    'max_dim' => 1600,
    ...$preset,
]);
                try {
                    $binary = (new MockupRenderer())->render([
                        'base_path' => $base->getPath(),
                        'shirt_mask_path' => $mask->getPath(),
                        'shirt_shadow_path' => $shadow?->getPath(),
                        'design_path' => $design->getPath(),
                        'warp_points' => $warp,
                        'render_mode' => $renderMode,
                        'max_dim' => 1600,
                        ...$preset,
                    ]);

                    if (empty($binary)) {
                        Log::warning("MockupRenderer returned empty binary for mockup {$mockup->id} side {$side}");
                        continue;
                    }

                    $renderTmpPath = storage_path(
                        'app/tmp_uploads/render_' . $mockup->id . '_' . $side . '_' . uniqid() . '.png'
                    );

                    file_put_contents($renderTmpPath, $binary);
                    unset($binary);

                    if (!$this->isValidImage($renderTmpPath)) {
                        @unlink($renderTmpPath);
                        Log::warning("Rendered PNG is corrupt for mockup {$mockup->id} side {$side}");
                        continue;
                    }

                    // احذف الرندر القديم
                    $template->getMedia('rendered_mockups')
                        ->filter(fn($media) => $media->getCustomProperty('side') === $side &&
                            (int)$media->getCustomProperty('category_id') === (int)$mockup->category_id
                        )
                        ->each
                        ->delete();

                    $template->addMedia($renderTmpPath)
                        ->usingFileName("tpl_{$template->id}_{$side}_cat{$mockup->category_id}.png")
                        ->withCustomProperties([
                            'side' => $side,
                            'template_id' => (string)$template->id,
                            'category_id' => (int)$mockup->category_id,
                            'render_mode' => $renderMode, // ← احفظ الوضع المستخدم
                        ])
                        ->toMediaCollection('rendered_mockups');

                    gc_collect_cycles();

                } catch (\Throwable $e) {
                    Log::error("Render failed mockup {$mockup->id} side {$side} template {$template->id}: " . $e->getMessage());
                }
            }
        }
    }

    private function isValidImage(string $path): bool
    {
        if (!file_exists($path) || filesize($path) === 0) {
            return false;
        }

        return @getimagesize($path) !== false;
    }
}
