<?php

namespace App\Jobs;

use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessBase64Image implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $base64Image,
        public $template,
        public $collection = null
    ) {}

    public function handle(): void
    {
        ini_set('memory_limit', '512M');

        // ---- 1) Decode base64 ----
        if (preg_match('/^data:image\/(\w+);base64,/', $this->base64Image, $type)) {
            $imageData = substr($this->base64Image, strpos($this->base64Image, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new \Exception('Invalid image type');
            }

            $imageData = base64_decode($imageData);
            if ($imageData === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('Invalid base64 format');
        }

        $tempDir = storage_path('app/tmp_uploads');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFilePath = $tempDir . '/' . uniqid('preview_') . '.' . $type;

        if (file_put_contents($tempFilePath, $imageData) === false) {
            throw new \Exception('Failed to write temp file');
        }

        // Free raw string immediately
        unset($imageData);

        // ---- 2) Validate before passing to Spatie ----
        if (!$this->isValidImage($tempFilePath)) {
            @unlink($tempFilePath);
            throw new \Exception('Written temp file is not a valid image');
        }

        // ---- 3) Save to media collection ----
        $this->template->clearMediaCollection($this->collection);
        $this->template->addMedia($tempFilePath)
            ->toMediaCollection($this->collection);

        // Spatie moves the file — no manual unlink needed
        unset($tempFilePath);

        // Force GC before heavy rendering
        gc_collect_cycles();

        // ---- 4) Render mockups ----
        if (get_class($this->template) === Template::class) {
            $this->renderMockups();
        }
    }

    private function isValidImage(string $path): bool
    {
        if (!file_exists($path) || filesize($path) === 0) return false;

        return @getimagesize($path) !== false;
    }

    private function renderMockups(): void
    {
        $template = $this->template->fresh(['mockups.types', 'mockups.media']);

        $mockups = $template->mockups;

        if (!$mockups || $mockups->isEmpty()) return;

        foreach ($mockups as $mockup) {

            // ---- Read positions from pivot ----
            $positions = $mockup->pivot->positions ?? [];
            if (is_string($positions)) {
                $positions = json_decode($positions, true) ?? [];
            }

            foreach ($mockup->types as $type) {
                $side = strtolower($type->value->name);

                $expectedCollection = $side === 'back' ? 'back_templates' : 'templates';
                if ($this->collection !== $expectedCollection) continue;

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

                $design = $template->getFirstMedia($this->collection);

                if (!$design || !file_exists($design->getPath())) continue;

                // ---- Validate design file before rendering ----
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

                    // Write rendered PNG to temp file
                    $renderTmpPath = storage_path("app/tmp_uploads/render_{$mockup->id}_{$side}_" . uniqid() . '.png');
                    file_put_contents($renderTmpPath, $binary);

                    // Free binary immediately after writing
                    unset($binary);

                    if (!$this->isValidImage($renderTmpPath)) {
                        @unlink($renderTmpPath);
                        Log::warning("Rendered PNG is corrupt for mockup {$mockup->id} side {$side}");
                        continue;
                    }

                    // Clear old rendered mockup for this side + category
                    $template->getMedia('rendered_mockups')
                        ->filter(fn($m) =>
                            $m->getCustomProperty('side') === $side &&
                            $m->getCustomProperty('category_id') === $mockup->category_id
                        )
                        ->each->delete();

                    // Use addMedia (file path) instead of addMediaFromString
                    // to avoid holding two large PNG copies in memory
                    $template->addMedia($renderTmpPath)
                        ->usingFileName("tpl_{$template->id}_{$side}_cat{$mockup->category_id}.png")
                        ->withCustomProperties([
                            'side'        => $side,
                            'template_id' => $template->id,
                            'category_id' => $mockup->category_id,
                        ])
                        ->toMediaCollection('rendered_mockups');

                    // Spatie moves the file — no manual unlink needed
                    gc_collect_cycles();

                } catch (\Throwable $e) {
                    Log::error("Render failed mockup {$mockup->id} side {$side} template {$template->id}: " . $e->getMessage());
                }
            }
        }
    }
}
