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
        $mockups = $mockupRepository->query()
            ->where('category_id',$model->category_id)
            ->whereKeyNot($model->id)
            ->whereHas('templates', function ($query) use ($model) {
                $query->whereIn('id', $model->templates->pluck('id')->toArray());
            })->get();
        $modelTemplateIds = $model->templates->pluck('id')->sort()->values();

        $matchingMockups = $mockups->filter(function ($m) use ($modelTemplateIds) {
            $tplIds = $m->templates->pluck('id')->sort()->values();
            return $tplIds->equals($modelTemplateIds);
        });


        $model->load(['templates', 'types', 'category']);

        foreach ($model->templates as $template) {

            $pivotPositions = $template->pivot->positions ?? [];
            $pivotColors    = $template->pivot->colors ?? [];

            if (!is_array($pivotColors)) $pivotColors = [];
            $colorsToRender = count($pivotColors) ? $pivotColors : [null];

            collect($model->types)->each(function ($type) use ($model, $template, $pivotPositions, $colorsToRender) {
                $sideName = strtolower($type->value->name);

                // ----- base & mask media -----
                $baseMedia = $model->getMedia('mockups')
                    ->first(fn ($m) =>
                        $m->getCustomProperty('side') === $sideName &&
                        $m->getCustomProperty('role') === 'base'
                    );

                $maskMedia = $model->getMedia('mockups')
                    ->first(fn ($m) =>
                        $m->getCustomProperty('side') === $sideName &&
                        $m->getCustomProperty('role') === 'mask'
                    );

                if (!$baseMedia || !$maskMedia) {
                    return;
                }

                $designMedia = ($sideName === 'back')
                    ? $template->getFirstMedia('back_templates')
                    : $template->getFirstMedia('templates');

                if (!$designMedia || !$designMedia->getPath()) {
                    throw new \Exception("Missing design media for {$sideName}");
                }

                $basePath = $baseMedia->getPath();
                [$baseWidth, $baseHeight] = getimagesize($basePath);

                // القيم جاية كنِسَب 0..1 من مساحة الموكاب
                $xPct  = (float)($pivotPositions[$sideName . '_x']      ?? 0.5);
                $yPct  = (float)($pivotPositions[$sideName . '_y']      ?? 0.5);
                $wPct  = (float)($pivotPositions[$sideName . '_width']  ?? 0.4);
                $hPct  = (float)($pivotPositions[$sideName . '_height'] ?? 0.4);
                $angle = (float)($pivotPositions[$sideName . '_angle']  ?? 0);

                $printW = max(1, (int) round($wPct * $baseWidth));
                $printH = max(1, (int) round($hPct * $baseHeight));

                $centerX = $xPct * $baseWidth;
                $centerY = $yPct * $baseHeight;

                $printX = (int) round($centerX - $printW / 2);
                $printY = (int) round($centerY - $printH / 2);

                if ($printW <= 0) $printW = (int) round($baseWidth * 0.3);
                if ($printH <= 0) $printH = (int) round($baseHeight * 0.3);

                // ✅ IMPORTANT: generate ONE image per color for THIS template
                foreach ($colorsToRender as $hex) {
                    $binary = (new MockupRenderer())->render([
                        'base_path'   => $basePath,
                        'shirt_path'  => $maskMedia->getPath(),
                        'design_path' => $designMedia->getPath(),
                        'print_x'     => $printX,
                        'print_y'     => $printY,
                        'print_w'     => $printW,
                        'print_h'     => $printH,
                        'angle'       => $angle ?? 0,
                        'hex'         => $hex, // ✅ template color (not first mockup)
                    ]);

                    $safeHex = $hex ? ltrim(strtolower($hex), '#') : 'no-color';

                    $model
                        ->addMediaFromString($binary)
                        ->usingFileName("mockup_{$sideName}_tpl{$template->id}_{$safeHex}.png")
                        ->withCustomProperties([
                            'side'        => $sideName,
                            'template_id' => $template->id,
                            'hex'         => $hex,
                        ])
                        ->toMediaCollection('generated_mockups');
                }
            });
        }
    }


}
