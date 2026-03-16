<?php

namespace App\Traits;

use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Facades\Log;

trait RendersTemplateMockups
{
    private function renderMockups($template, string $collection): void
    {
        $template = $template->fresh(['mockups.types', 'mockups.media']);

        $mockups = $template->mockups;

        if (!$mockups || $mockups->isEmpty()) return;

        foreach ($mockups as $mockup) {

            $positions = $mockup->pivot->positions ?? [];
            if (is_string($positions)) {
                $positions = json_decode($positions, true) ?? [];
            }

            foreach ($mockup->types as $type) {
                $side = strtolower($type->value->name);

                $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';
                if ($collection !== $expectedCollection) continue;

                $base = $mockup->getMedia('mockups')
                    ->first(fn($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'base'
                    );

                $mask = $mockup->getMedia('mockups')
                    ->first(fn($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'mask'
                    );

                if (!$base || !$mask) continue;

                $design = $template->getFirstMedia($collection);

                if (!$design || !file_exists($design->getPath())) continue;

                if (!$this->isValidImage($design->getPath())) {
                    Log::warning("Design file invalid/truncated: {$design->getPath()}");
                    continue;
                }

                [$baseW, $baseH] = getimagesize($base->getPath());

                $xPct  = (float)($positions["{$side}_x"]      ?? 0.5);
                $yPct  = (float)($positions["{$side}_y"]      ?? 0.5);
                $wPct  = (float)($positions["{$side}_width"]  ?? 0.4);
                $hPct  = (float)($positions["{$side}_height"] ?? 0.4);
                $angle = (float)($positions["{$side}_angle"]  ?? 0);

                $printW = max(1, (int)round($wPct * $baseW));
                $printH = max(1, (int)round($hPct * $baseH));
                $printX = (int)round($xPct * $baseW - $printW / 2);
                $printY = (int)round($yPct * $baseH - $printH / 2);

                try {
                    $binary = (new MockupRenderer())->render([
                        'base_path'   => $base->getPath(),
                        'shirt_path'  => $mask->getPath(),
                        'design_path' => $design->getPath(),
                        'print_x'     => $printX,
                        'print_y'     => $printY,
                        'print_w'     => $printW,
                        'print_h'     => $printH,
                        'angle'       => $angle,
                    ]);

                    if (empty($binary)) {
                        Log::warning("MockupRenderer returned empty binary for mockup {$mockup->id} side {$side}");
                        continue;
                    }

                    $renderTmpPath = storage_path("app/tmp_uploads/render_{$mockup->id}_{$side}_" . uniqid() . '.png');
                    file_put_contents($renderTmpPath, $binary);
                    unset($binary);

                    if (!$this->isValidImage($renderTmpPath)) {
                        @unlink($renderTmpPath);
                        Log::warning("Rendered PNG is corrupt for mockup {$mockup->id} side {$side}");
                        continue;
                    }

                    $template->getMedia('rendered_mockups')
                        ->filter(fn($m) =>
                            $m->getCustomProperty('side') === $side &&
                            (int)$m->getCustomProperty('category_id') === (int)$mockup->category_id
                        )
                        ->each->delete();

                    $template->addMedia($renderTmpPath)
                        ->usingFileName("tpl_{$template->id}_{$side}_cat{$mockup->category_id}.png")
                        ->withCustomProperties([
                            'side'        => $side,
                            'template_id' => (string)$template->id,
                            'category_id' => (int)$mockup->category_id,
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
        if (!file_exists($path) || filesize($path) === 0) return false;

        return @getimagesize($path) !== false;
    }
}
