<?php

namespace App\Jobs;

use App\Models\Mockup;
use App\Models\Template;
use App\Services\Mockup\MockupRenderer;
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
        public Mockup $mockup
    )
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

            // ألوان جديدة من التمبلت
            $newColors = collect($template->pivot->colors ?? [])
                ->map(fn($c) => strtolower($c))
                ->filter()
                ->unique();

            // تحميل mockups قديمة بنفس التصنيف والتمبلت
            $oldMockups = Mockup::query()
                ->where('category_id', $model->category_id)
                ->whereKeyNot($model->id)
                ->whereHas('templates', fn($q) => $q->where('templates.id', $templateId))
                ->with(['templates', 'types', 'media'])
                ->get();

            // ألوان موجودة في mockups قديمة
            $oldColors = $oldMockups
                ->flatMap(fn($m) => $m->templates->firstWhere('id', $templateId)?->pivot->colors ?? [])
                ->map(fn($c) => strtolower($c))
                ->filter()
                ->unique();

            // دمج كل الألوان
            $allColors = $newColors
                ->merge($oldColors)
                ->unique()
                ->values()
                ->all();

            // تحديد الألوان المحذوفة
            $removedColors = collect($oldColors ?? [])
                ->diff($newColors)
                ->values()
                ->all();

            // مسح اللون المحذوف من جميع mockups الأخرى
            foreach ($removedColors as $hex) {
                $otherMockups = Mockup::query()
                    ->where('category_id', $model->category_id)
                    ->whereKeyNot($model->id)
                    ->whereHas('templates', fn($q) => $q->where('templates.id', $templateId))
                    ->with('templates')
                    ->get();

                foreach ($otherMockups as $otherMockup) {
                    $otherTemplate = $otherMockup->templates->firstWhere('id', $templateId);
                    if (!$otherTemplate) continue;

                    $colors = collect($otherTemplate->pivot->colors ?? [])
                        ->filter(fn($c) => strtolower($c) !== strtolower($hex))
                        ->values()
                        ->all();

                    // تحديث pivot بدون اللون المحذوف
                    $otherMockup->templates()->updateExistingPivot($templateId, [
                        'colors' => $colors
                    ]);

                    // مسح الصور القديمة الخاصة بالتمبلت واللون ده فقط
                    $otherMockup->getMedia('generated_mockups')
                        ->filter(fn($media) =>
                            $media->getCustomProperty('template_id') === $templateId &&
                            strtolower($media->getCustomProperty('hex')) === strtolower($hex)
                        )
                        ->each(fn($media) => $media->delete());
                }
            }

            // تحديث التمبلت الجديد
            $missingForNew = collect($allColors)->diff($newColors)->values()->all();
            $colorsToRenderForNew = $allColors;

            $model->templates()->updateExistingPivot($templateId, [
                'colors' => array_values(array_unique(
                    array_merge($newColors->all(), $missingForNew)
                )),
            ]);

            // مسح الصور القديمة الخاصة بالتمبلت ده فقط قبل الرندر
            $model->getMedia('generated_mockups')
                ->filter(fn($media) => $media->getCustomProperty('template_id') === $templateId)
                ->each(fn($media) => $media->delete());

            $this->renderMockupColors(
                mockup: $model,
                template: $template,
                colors: $colorsToRenderForNew
            );


            // تحديث mockups قديمة
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

                // تحديث بيانات pivot
                $pivotData = [];

                if (!empty($missingForOld)) {
                    $pivotData['colors'] = array_values(array_unique(
                        array_merge($oldTplColors, $missingForOld)
                    ));
                }

                // تحديث الـpositions لو اتغيرت
//                $pivotData['positions'] = $template->pivot->positions ?? [];

                // مسح الصور القديمة الخاصة بالتمبلت ده فقط
                $oldMockup->getMedia('generated_mockups')
                    ->filter(fn($media) => $media->getCustomProperty('template_id') === $templateId)
                    ->each(fn($media) => $media->delete());

                $oldMockup->templates()->updateExistingPivot($templateId, $pivotData);

                // إعادة الرندر لكل الألوان القديمة والجديدة
                $this->renderMockupColors(
                    mockup: $oldMockup,
                    template: $oldTemplate,
                    colors: array_merge($oldTplColors, $missingForOld)
                );
            }
        }
    }
}
