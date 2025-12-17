<?php

namespace App\Jobs;

use App\Enums\Mockup\TypeEnum;
use App\Models\Mockup;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HandleMockupFilesJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Mockup $mockup)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(MockupRepositoryInterface $mockupRepository): void
    {
        $model = $this->mockup;

        // 🟢 احضر كل mockups في نفس الـ category وبنفس الـ templates
        $mockups = $mockupRepository->query()
            ->where('category_id', $model->category_id)
            ->whereKeyNot($model->id)
            ->whereHas('templates', fn($q) =>
            $q->whereIn('templates.id', $model->templates->pluck('id'))
            )
            ->with(['templates', 'types', 'media'])
            ->get();

        $model->load(['templates', 'types', 'category', 'media']);

        foreach ($model->templates as $template) {
            $templateId = $template->id;
            $pivotPositions = $template->pivot->positions ?? [];
            $pivotColors = $template->pivot->colors ?? [];

            if (is_string($pivotColors)) {
                $pivotColors = json_decode($pivotColors, true) ?: [];
            }
            if (!is_array($pivotColors)) $pivotColors = [];

            // ألوان النموذج الحالي
            $modelColors = collect($pivotColors)->filter()->unique()->values()->all();

            // 🟣 ابحث عن mockups لها نفس الـ template
            $matchingMockups = $mockups->filter(function ($m) use ($templateId) {
                return $m->templates->pluck('id')->contains($templateId);
            });

            // 🧩 لكل mockup مطابق، شوف الفرق في الألوان
            foreach ($matchingMockups as $otherMockup) {
                $otherTemplate = $otherMockup->templates->firstWhere('id', $templateId);
                if (!$otherTemplate) continue;

                $otherColors = $otherTemplate->pivot->colors ?? [];
                if (is_string($otherColors)) {
                    $otherColors = json_decode($otherColors, true) ?: [];
                }
                if (!is_array($otherColors)) $otherColors = [];

                // احسب الألوان الجديدة اللي مش موجودة في الآخر
                $missingColors = collect($modelColors)
                    ->diff($otherColors)
                    ->filter()
                    ->values()
                    ->all();

                if (empty($missingColors)) continue; // ما فيش جديد

                // 🔄 حدّث pivot بالألوان الجديدة
                $newColors = array_values(array_unique(array_merge($otherColors, $missingColors)));
                $otherMockup->templates()->updateExistingPivot($templateId, [
                    'colors' => json_encode($newColors),
                ]);

                // 🧠 حمّل البيانات اللازمة
                $otherMockup->loadMissing(['types', 'media']);
                foreach ($missingColors as $hex) {
                    foreach ($otherMockup->types as $type) {
                        $sideName = strtolower($type->value->name);

                        $baseMedia = $otherMockup->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName && $m->getCustomProperty('role') === 'base');
                        $maskMedia = $otherMockup->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName && $m->getCustomProperty('role') === 'mask');
                        if (!$baseMedia || !$maskMedia) continue;

                        $designMedia = ($sideName === 'back')
                            ? $template->getFirstMedia('back_templates')
                            : $template->getFirstMedia('templates');
                        if (!$designMedia || !$designMedia->getPath()) continue;

                        [$baseW, $baseH] = getimagesize($baseMedia->getPath());

                        $xPct  = (float)($pivotPositions["{$sideName}_x"] ?? 0.5);
                        $yPct  = (float)($pivotPositions["{$sideName}_y"] ?? 0.5);
                        $wPct  = (float)($pivotPositions["{$sideName}_width"] ?? 0.4);
                        $hPct  = (float)($pivotPositions["{$sideName}_height"] ?? 0.4);
                        $angle = (float)($pivotPositions["{$sideName}_angle"] ?? 0);

                        $printW = max(1, (int) round($wPct * $baseW));
                        $printH = max(1, (int) round($hPct * $baseH));
                        $printX = (int) round($xPct * $baseW - $printW / 2);
                        $printY = (int) round($yPct * $baseH - $printH / 2);

                        try {
                            $binary = (new MockupRenderer())->render([
                                'base_path'   => $baseMedia->getPath(),
                                'shirt_path'  => $maskMedia->getPath(),
                                'design_path' => $designMedia->getPath(),
                                'print_x'     => $printX,
                                'print_y'     => $printY,
                                'print_w'     => $printW,
                                'print_h'     => $printH,
                                'angle'       => $angle,
                                'hex'         => $hex,
                            ]);

                            $safeHex = $hex ? ltrim(strtolower($hex), '#') : 'no-color';
                            $otherMockup
                                ->addMediaFromString($binary)
                                ->usingFileName("mockup_{$sideName}_tpl{$template->id}_{$safeHex}.png")
                                ->withCustomProperties([
                                    'side'        => $sideName,
                                    'template_id' => $template->id,
                                    'hex'         => $hex,
                                    'category_id' => $model->category_id,
                                ])
                                ->toMediaCollection('generated_mockups');
                        } catch (\Throwable $e) {
                            \Log::error("Render failed for mockup {$otherMockup->id} color {$hex}: ".$e->getMessage());
                        }
                    }
                }
            }
        }
    }


}
