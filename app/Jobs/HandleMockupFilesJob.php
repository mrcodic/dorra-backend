<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
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
    public function handle(): void
    {
        $model = $this->mockup;

        foreach ($model->templates as $template) {

            $templateId = $template->id;

            /** ================== NEW MOCKUP COLORS ================== */
            $newColors = collect($template->pivot->colors ?? [])
                ->map(fn ($c) => strtolower($c))
                ->filter()
                ->unique()
                ->values()
                ->all();

            /** ================== LOAD OLD MOCKUPS ================== */
            $oldMockups = Mockup::query()
                ->where('category_id', $model->category_id)
                ->whereKeyNot($model->id)
                ->whereHas('templates', fn ($q) =>
                $q->where('templates.id', $templateId)
                )
                ->with(['templates', 'types', 'media'])
                ->get();

            /** ================== SYNC COLORS ================== */
            foreach ($oldMockups as $oldMockup) {

                $oldTemplate = $oldMockup->templates->firstWhere('id', $templateId);
                if (!$oldTemplate) continue;

                $oldColors = collect($oldTemplate->pivot->colors ?? [])
                    ->map(fn ($c) => strtolower($c))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                // لو لون اتمسح من الجديد → يتمسح من القديم
                $colorsToKeep = array_values(array_intersect($oldColors, $newColors));

                // لو لون جديد → يضاف للقديم
                $colorsToAdd = array_values(array_diff($newColors, $oldColors));

                $newPositions = $template->pivot->positions ?? [];
                $oldPositions = $oldTemplate->pivot->positions ?? [];

                $positionsChanged = json_encode($newPositions) !== json_encode($oldPositions);

                // تحديث الـ pivot
                $oldMockup->templates()->updateExistingPivot($templateId, [
                    'colors'    => array_values(array_unique(
                        array_merge($colorsToKeep, $colorsToAdd)
                    )),
                    'positions' => $newPositions,
                ]);

                // لو البوزيشنز اتغيرت → نعيد رندر كل الألوان
                if ($positionsChanged) {

                    $oldMockup->media()
                        ->where('collection_name', 'generated_mockups')
                        ->where('custom_properties->template_id', $templateId)
                        ->delete();

                    $this->renderMockupColors(
                        mockup: $oldMockup,
                        template: $oldTemplate->refresh(),
                        colors: array_values(array_unique(
                            array_merge($colorsToKeep, $colorsToAdd)
                        ))
                    );

                    continue;
                }

                // رندر الألوان الجديدة فقط
                if (!empty($colorsToAdd)) {
                    $this->renderMockupColors(
                        mockup: $oldMockup,
                        template: $oldTemplate,
                        colors: $colorsToAdd
                    );
                }
            }

            /** ================== UPDATE NEW MOCKUP ================== */
            $model->templates()->updateExistingPivot($templateId, [
                'colors' => $newColors,
            ]);

            $this->renderMockupColors(
                mockup: $model,
                template: $template,
                colors: $newColors
            );
        }
    }
}
