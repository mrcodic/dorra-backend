<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
use App\Services\Mockup\MockupRenderConfigResolver;
use App\Services\Mockup\MockupRenderModeResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RenderMockupUnitJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $mockupId,
        public string $templateId,
        public string $side,
        public string $hex,
    ) {}

    public function handle(): void
    {
        $mockup   = Mockup::with(['media'])->find($this->mockupId);
        $template = Template::find($this->templateId);

        if (!$mockup || !$template) return;

        $side = strtolower($this->side);
        $hex  = strtolower($this->hex);

        try {
            [$base, $mask, $shadow] = $this->getAssets($mockup, $side);

            if (!$base || !$mask) {
                Log::warning("Missing base or mask", ['mockup' => $this->mockupId, 'side' => $side]);
                return;
            }

            $design = $this->getDesign($template, $side);

            if (!$design) {
                Log::warning("Missing design", [
                    'template'  => $this->templateId,
                    'approach'  => $template->approach,
                    'side'      => $side,
                ]);
                return;
            }

            $modeResolver   = app(MockupRenderModeResolver::class);
            $configResolver = app(MockupRenderConfigResolver::class);

            // build context the same way the debug route does
            $maskBounds = $this->getMaskBounds($mask);
            $context    = $this->getContext($design, $maskBounds);

            $mode   = $modeResolver->resolve($context);
            $config = $configResolver->resolve($mockup, $side, $mode);

            $binary = (new MockupRenderer())->render([
                'base_path'         => $base->getPath(),
                'shirt_mask_path'   => $mask->getPath(),
                'shirt_shadow_path' => $shadow?->getPath(),
                'design_path'       => $design->getPath(),   // ← was missing / null before
                'warp_points'       => $config['warp_points'],
                'render_mode'       => $config['render_mode'],
                'hex'               => $hex,
                'max_dim'           => 1600,
                ...$config['preset'],
            ]);

            if (empty($binary)) {
                Log::warning("Empty binary returned", ['mockup' => $this->mockupId]);
                return;
            }

            $this->store($mockup, $template, $side, $hex, $binary);

        } catch (\Throwable $e) {
            Log::error("Render failed {$this->mockupId} {$this->hex}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    private function getAssets($mockup, $side)
    {
        return [
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'base')),
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'mask')),
            $mockup->getMedia('mockups')->first(fn($m) => $this->match($m, $side, 'shadow')),
        ];
    }

    private function match($m, $side, $role): bool
    {
        return $m->getCustomProperty('side') === $side
            && $m->getCustomProperty('role') === $role;
    }

    private function getDesign($template, string $side): ?object
    {
        $media = $side === 'back'
            ? ($template->approach === 'without_editor'
                ? $template->getFirstMedia('back-templates-preview')
                : $template->getFirstMedia('back_templates'))
            : ($template->approach === 'without_editor'
                ? $template->getFirstMedia('templates-preview')
                : $template->getFirstMedia('templates'));

        Log::info('getDesign', [
            'template_id'  => $template->id,
            'approach'     => $template->approach,
            'side'         => $side,
            'media_id'     => $media?->id,
            'path'         => $media?->getPath(),
            'path_exists'  => $media ? file_exists($media->getPath()) : false,
        ]);

        return ($media && file_exists($media->getPath())) ? $media : null;
    }
    private function getMaskBounds($mask): array
    {
        $img = new \Imagick($mask->getPath());
        $tmp = clone $img;
        $tmp->trimImage(0);
        $page = $tmp->getImagePage();   // ← debug route uses getImagePage(), not just size

        $bounds = [
            'x' => max(0, (int) ($page['x'] ?? 0)),
            'y' => max(0, (int) ($page['y'] ?? 0)),
            'w' => max(1, $tmp->getImageWidth()),
            'h' => max(1, $tmp->getImageHeight()),
        ];

        $tmp->clear();
        $img->clear();

        return $bounds;
    }

    private function getContext($design, array $maskBounds): array
    {
        $img = new \Imagick($design->getPath());

        $w = $img->getImageWidth();
        $h = $img->getImageHeight();

        $ctx = [
            'coverage_ratio'       => (($w / $maskBounds['w']) + ($h / $maskBounds['h'])) / 2,
            'placed_width_ratio'   => $w / $maskBounds['w'],
            'placed_height_ratio'  => $h / $maskBounds['h'],
            'has_alpha'            => $img->getImageAlphaChannel() !== 0,
            'mime'                 => $design->mime_type ?? 'image/png',
        ];

        $img->clear();

        return $ctx;
    }
    private function store($mockup, $template, $side, $hex, $binary)
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
