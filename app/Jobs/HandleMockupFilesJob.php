<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HandleMockupFilesJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Mockup $mockup)
    {
    }

    /**
     * Render colors for specific mockup + template
     */
    private function renderMockupColors(Mockup $mockup, Template $template, array $colors): void
    {
        if (empty($colors)) return;

        $mockup->loadMissing(['types', 'media']);
        $attempts = 0;
        while ($attempts < 3) {
            $design = $template->getFirstMedia('templates');
            $backDesign = $template->getFirstMedia('back_templates');
            if ($design && $design->getPath()) break;
            usleep(500_000); // نص ثانية
            $attempts++;
            $mockup->refresh();
        }
        $positions = $template->pivot->positions ?? [];

        foreach ($colors as $hex) {
            foreach ($mockup->types as $type) {

                $side = strtolower($type->value->name);

                $base = $mockup->getMedia('mockups')
                    ->first(fn ($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'base'
                    );

                $mask = $mockup->getMedia('mockups')
                    ->first(fn ($m) =>
                        $m->getCustomProperty('side') === $side &&
                        $m->getCustomProperty('role') === 'mask'
                    );

                if (!$base || !$mask) continue;

                $design = $side === 'back'
                    ? $template->getFirstMedia('back_templates')
                    : $template->getFirstMedia('templates');

                if (!$design || !$design->getPath()) continue;

                [$baseW, $baseH] = getimagesize($base->getPath());

                $xPct  = (float)($positions["{$side}_x"] ?? 0.5);
                $yPct  = (float)($positions["{$side}_y"] ?? 0.5);
                $wPct  = (float)($positions["{$side}_width"] ?? 0.4);
                $hPct  = (float)($positions["{$side}_height"] ?? 0.4);
                $angle = (float)($positions["{$side}_angle"] ?? 0);

                $printW = max(1, (int) round($wPct * $baseW));
                $printH = max(1, (int) round($hPct * $baseH));
                $printX = (int) round($xPct * $baseW - $printW / 2);
                $printY = (int) round($yPct * $baseH - $printH / 2);

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
                        'hex'         => $hex,
                    ]);

                    $safeHex = ltrim(strtolower($hex), '#');

                    $mockup->addMediaFromString($binary)
                        ->usingFileName("mockup_{$side}_tpl{$template->id}_{$safeHex}.png")
                        ->withCustomProperties([
                            'side'        => $side,
                            'template_id' => $template->id,
                            'hex'         => $hex,
                            'category_id' => $mockup->category_id,
                        ])
                        ->toMediaCollection('generated_mockups');

                } catch (\Throwable $e) {
                    Log::error("Render failed mockup {$mockup->id} {$hex}: ".$e->getMessage());
                }
            }
        }
    }

    /**
     * Execute the job.
     */
    public function handle(MockupRepositoryInterface $mockupRepository): void
    {
        $model = $this->mockup;


        foreach ($model->templates as $template) {

            $templateId = $template->id;

            /** ------------------ COLORS FROM NEW MOCKUP ------------------ */
            $newColors = collect($template->pivot->colors ?? [])
                ->filter()
                ->unique();

            /** ------------------ LOAD OLD MOCKUPS ------------------ */
            $oldMockups = Mockup::query()
                ->where('category_id', $model->category_id)
                ->whereKeyNot($model->id)
                ->whereHas('templates', fn ($q) =>
                $q->where('templates.id', $templateId)
                )
                ->with(['templates', 'types', 'media'])
                ->get();

            /** ------------------ COLORS FROM OLD MOCKUPS ------------------ */
            $oldColors = $oldMockups
                ->flatMap(function ($m) use ($templateId) {
                    $tpl = $m->templates->firstWhere('id', $templateId);
                    return $tpl?->pivot->colors ?? [];
                })
                ->filter()
                ->unique();

            /** ------------------ MERGE ALL COLORS ------------------ */
            $allColors = $newColors
                ->merge($oldColors)
                ->unique()
                ->values()
                ->all();

            /** ================== UPDATE NEW MOCKUP ================== */
            $missingForNew = collect($allColors)
                ->diff($newColors)
                ->values()
                ->all();

            $colorsToRenderForNew = !empty($missingForNew) ? $missingForNew : $newColors->all();

            $model->templates()->updateExistingPivot($templateId, [
                'colors' => array_values(array_unique(
                    array_merge($newColors->all(), $missingForNew)
                )),
            ]);

            $this->renderMockupColors(
                mockup: $model,
                template: $template,
                colors: $colorsToRenderForNew
            );

            /** ================== UPDATE OLD MOCKUPS ================== */
            foreach ($oldMockups as $oldMockup) {

                $oldTemplate = $oldMockup->templates->firstWhere('id', $templateId);
                if (!$oldTemplate) continue;

                $oldTplColors = collect($oldTemplate->pivot->colors ?? [])
                    ->filter()
                    ->values()
                    ->all();

                $missingForOld = collect($allColors)
                    ->diff($oldTplColors)
                    ->values()
                    ->all();

                if (empty($missingForOld)) continue;

                $oldMockup->templates()->updateExistingPivot($templateId, [
                    'colors' => array_values(array_unique(
                        array_merge($oldTplColors, $missingForOld)
                    )),
                ]);

                $this->renderMockupColors(
                    mockup: $oldMockup,
                    template: $oldTemplate,
                    colors: $missingForOld
                );
            }

        }
    }
}
