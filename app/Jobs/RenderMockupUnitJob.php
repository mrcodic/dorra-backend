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
        $lockKey = "render_{$this->mockupId}_{$this->templateId}_{$this->side}_{$this->hex}";

//        if (!Cache::lock($lockKey, 5)->get()) {
//            return;
//        }

        $mockup = Mockup::with(['media'])->find($this->mockupId);
        $template = Template::find($this->templateId);

        if (!$mockup || !$template) return;

        $side = strtolower($this->side);
        $hex = strtolower($this->hex);

        try {
            [$base, $mask, $shadow] = $this->getAssets($mockup, $side);
            if (!$base || !$mask) return;

            $design = $this->getDesign($template, $side);
            if (!$design) return;

            $maskBounds = $this->getMaskBounds($mask);
            $context = $this->getContext($design, $maskBounds);

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

            $this->store($mockup, $template, $side, $hex, $binary);

        } catch (\Throwable $e) {
            Log::error("Render failed {$this->mockupId} {$this->hex}: " . $e->getMessage());
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

    private function getDesign($template, $side)
    {
        $media = $side === 'back'
            ?($template->approach == 'without_editor' ? $template->getFirstMedia('back-templates-preview') : $template->getFirstMedia('back_templates'))
            :($template->approach == 'without_editor' ? $template->getFirstMedia('templates-preview') :$template->getFirstMedia('templates'));

        return ($media && file_exists($media->getPath())) ? $media : null;
    }

    private function getMaskBounds($mask)
    {
        $img = new \Imagick($mask->getPath());
        $tmp = clone $img;

        $tmp->trimImage(0);

        $bounds = [
            'w' => $tmp->getImageWidth(),
            'h' => $tmp->getImageHeight(),
        ];

        $tmp->clear();
        $img->clear();

        return $bounds;
    }

    private function getContext($design, $maskBounds)
    {
        $img = new \Imagick($design->getPath());

        $w = $img->getImageWidth();
        $h = $img->getImageHeight();

        $ctx = [
            'coverage_ratio' => (($w / $maskBounds['w']) + ($h / $maskBounds['h'])) / 2,
            'placed_width_ratio' => $w / $maskBounds['w'],
            'placed_height_ratio' => $h / $maskBounds['h'],
            'has_alpha' => $img->getImageAlphaChannel() !== 0,
            'mime' => $design->mime_type ?? 'image/png',
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
