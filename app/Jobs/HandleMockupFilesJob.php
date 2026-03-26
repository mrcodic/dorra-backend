<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
use App\Services\Mockup\MockupRenderConfigResolver;
use App\Services\Mockup\MockupRenderModeResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleMockupFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    public function __construct(
        public Mockup $mockup,
        public $type = 'update',
    ) {}

    public function handle(): void
    {
        $model = $this->mockup;

        foreach ($model->templates as $template) {
            $templateId = $template->id;

            $newColors = $this->normalizeColors($template->pivot->colors ?? []);

            $oldMockups = $this->getRelatedMockups($model, $templateId);

            $oldColors = $this->extractOldColors($oldMockups, $templateId);

            $allColors = $this->mergeColors($newColors, $oldColors);

            if ($this->type === 'create') {
                $this->syncNewMockupColors($model, $templateId, $newColors, $allColors);
            }

            $this->clearTemplateMedia($model, $templateId);

            $this->renderMockupColors($model, $template, $allColors);

            foreach ($oldMockups as $oldMockup) {
                $this->processOldMockup($oldMockup, $template, $templateId, $allColors);
            }
        }
    }

    // ================= HELPERS =================

    private function normalizeColors(array $colors)
    {
        return collect($colors)
            ->map(fn($c) => strtolower($c))
            ->filter()
            ->unique();
    }

    private function getRelatedMockups(Mockup $model, string $templateId)
    {
        return Mockup::query()
            ->where('category_id', $model->category_id)
            ->whereKeyNot($model->id)
            ->whereHas('templates', fn($q) => $q->where('templates.id', $templateId))
            ->with(['templates', 'types', 'media'])
            ->get();
    }

    private function extractOldColors($mockups, string $templateId)
    {
        return $mockups
            ->flatMap(fn($m) => $m->templates->firstWhere('id', $templateId)?->pivot->colors ?? [])
            ->map(fn($c) => strtolower($c))
            ->filter()
            ->unique();
    }

    private function mergeColors($new, $old): array
    {
        return $new->merge($old)->unique()->values()->all();
    }

    private function syncNewMockupColors(Mockup $model, string $templateId, $newColors, array $allColors)
    {
        $missing = collect($allColors)->diff($newColors)->values()->all();

        $model->templates()->updateExistingPivot($templateId, [
            'colors' => array_values(array_unique(
                array_merge($newColors->all(), $missing)
            )),
        ]);
    }

    private function processOldMockup(Mockup $mockup, Template $template, string $templateId, array $allColors)
    {
        $oldTemplate = $mockup->templates->firstWhere('id', $templateId);
        if (!$oldTemplate) return;

        $oldColors = collect($oldTemplate->pivot->colors ?? [])->filter()->values()->all();

        $missing = collect($allColors)->diff($oldColors)->values()->all();

        if (!empty($missing)) {
            $mockup->templates()->updateExistingPivot($templateId, [
                'colors' => array_values(array_unique(array_merge($oldColors, $missing))),
            ]);
        }

        $this->clearTemplateMedia($mockup, $templateId);

        $this->renderMockupColors(
            $mockup,
            $oldTemplate,
            array_merge($oldColors, $missing)
        );
    }

    private function clearTemplateMedia(Mockup $mockup, string $templateId): void
    {
        $mockup->getMedia('generated_mockups')
            ->filter(fn($m) =>
                (string)$m->getCustomProperty('template_id') === (string)$templateId
            )
            ->each->delete();
    }

    // ================= CORE RENDER =================

    private function renderMockupColors(Mockup $mockup, Template $template, array $colors): void
    {
        if (empty($colors)) return;

        $mockup->loadMissing(['types', 'media']);

        $positions = $this->parsePositions($template);

        foreach ($colors as $hex) {
            foreach ($mockup->types as $type) {
                $this->renderSingle($mockup, $template, $type->value->name, $hex, $positions);
            }
        }
    }

    private function renderSingle(Mockup $mockup, Template $template, string $side, string $hex, array $positions)
    {
        $side = strtolower($side);
        $hex = strtolower($hex);

        [$base, $mask, $shadow] = $this->getBaseAssets($mockup, $side);
        if (!$base || !$mask) return;

        $design = $this->getDesign($template, $side);
        if (!$design) return;

        try {
            $maskBounds = $this->getMaskBounds($mask);
            $context = $this->getDesignContext($design, $maskBounds);

            $modeResolver = app(MockupRenderModeResolver::class);
            $configResolver = app(MockupRenderConfigResolver::class);

            $mode = $modeResolver->resolve($context);

            $config = $configResolver->resolve($mockup, $side, $mode);

            $binary = (new MockupRenderer())->render([
                'base_path' => $base->getPath(),
                'shirt_mask_path' => $mask->getPath(),
                'shirt_shadow_path' => $shadow?->getPath(),
                'design_path' => $design->getPath(),
                'warp_points' => $config['warp_points'],
                'render_mode' => $config['render_mode'],
                'max_dim' => 1600,
                ...$config['preset'],
                'hex' => $hex,
            ]);

            if (empty($binary)) return;

            $this->storeGenerated($mockup, $template, $side, $hex, $binary);

        } catch (\Throwable $e) {
            Log::error("Render failed mockup {$mockup->id} {$hex}: " . $e->getMessage());
        }
    }

    // ================= LOW LEVEL =================

    private function getBaseAssets(Mockup $mockup, string $side)
    {
        return [
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'base')),
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'mask')),
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'shadow')),
        ];
    }

    private function match($media, $side, $role): bool
    {
        return $media->getCustomProperty('side') === $side
            && $media->getCustomProperty('role') === $role;
    }

    private function getDesign(Template $template, string $side)
    {
        $media = $side === 'back'
            ? $template->getFirstMedia('back_templates')
            : $template->getFirstMedia('templates');

        return ($media && file_exists($media->getPath())) ? $media : null;
    }

    private function parsePositions(Template $template): array
    {
        $positions = $template->pivot->positions ?? [];
        return is_string($positions) ? json_decode($positions, true) ?? [] : $positions;
    }

    private function getMaskBounds($mask): array
    {
        $img = new \Imagick($mask->getPath());
        $tmp = clone $img;

        $tmp->trimImage(0);
        $page = $tmp->getImagePage();

        $bounds = [
            'x' => (int)($page['x'] ?? 0),
            'y' => (int)($page['y'] ?? 0),
            'w' => $tmp->getImageWidth(),
            'h' => $tmp->getImageHeight(),
        ];

        $tmp->clear();
        $img->clear();

        return $bounds;
    }

    private function getDesignContext($design, array $maskBounds): array
    {
        $img = new \Imagick($design->getPath());

        $w = $img->getImageWidth();
        $h = $img->getImageHeight();

        $context = [
            'coverage_ratio' => (($w / $maskBounds['w']) + ($h / $maskBounds['h'])) / 2,
            'placed_width_ratio' => $w / $maskBounds['w'],
            'placed_height_ratio' => $h / $maskBounds['h'],
            'has_alpha' => $img->getImageAlphaChannel() !== 0,
            'mime' => $design->mime_type ?? 'image/png',
        ];

        $img->clear();

        return $context;
    }

    private function storeGenerated(Mockup $mockup, Template $template, string $side, string $hex, string $binary)
    {
        $safeHex = ltrim($hex, '#');

        $mockup->getMedia('generated_mockups')
            ->filter(fn($m) =>
                $m->getCustomProperty('template_id') == $template->id &&
                $m->getCustomProperty('side') == $side &&
                strtolower($m->getCustomProperty('hex')) == $hex
            )
            ->each->delete();

        $mockup->addMediaFromString($binary)
            ->usingFileName("mockup_{$side}_tpl{$template->id}_{$safeHex}.png")
            ->withCustomProperties([
                'side' => $side,
                'template_id' => (string)$template->id,
                'hex' => $hex,
                'category_id' => (int)$mockup->category_id,
            ])
            ->toMediaCollection('generated_mockups');
    }
}
