<?php

namespace App\Observers;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Design;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;

class MockupObserver
{
    public function updated(Mockup $mockup): void
    {
        if (!$mockup->wasChanged('pre_fill_colors')) {
            return;
        }

        $oldColors = $mockup->getOriginal('pre_fill_colors');

        if (is_string($oldColors)) {
            $oldColors = json_decode($oldColors, true) ?? [];
        }

        $oldHexes = collect($oldColors ?? [])
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->all();

        $newHexes = collect($mockup->pre_fill_colors ?? [])
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->all();

        $addedHexes = array_values(
            array_diff($newHexes, $oldHexes)
        );

        if (empty($addedHexes)) {
            return;
        }

        $this->generateForNewColors(
            $mockup,
            $addedHexes
        );
    }

    protected function generateForNewColors(
        Mockup $mockup,
        array $addedHexes
    ): void {
        $mockup->load([
            'templates',
            'sideSettings',
            'media',
        ]);

        if ($mockup->templates->isEmpty()) {
            return;
        }

        $hexToOriginalColor = collect(
            $mockup->pre_fill_colors ?? []
        )
            ->keyBy(fn ($color) => $this->normalizeHex($color))
            ->all();

        $renderJobs = [];

        foreach ($mockup->templates as $template) {

            /*
             * Try template custom positions first.
             */
            $positions = $this->getTemplatePositions($template);

            $usedDefaultPositions = false;

            /*
             * No custom positions?
             * Use MockupSideSetting.
             */
            if (empty($positions)) {
                $positions = $this->getDefaultPositions($mockup);
                $usedDefaultPositions = !empty($positions);
            }

            /*
             * No positions anywhere.
             */
            if (empty($positions)) {
                continue;
            }

            /*
             * Existing template colors.
             */
            $pivotColors = $template->pivot->colors ?? [];

            if (is_string($pivotColors)) {
                $pivotColors = json_decode(
                    $pivotColors,
                    true
                ) ?? [];
            }

            $colorsToAdd = collect($addedHexes)
                ->map(
                    fn ($hex) =>
                        $hexToOriginalColor[$hex] ?? $hex
                )
                ->all();

            $mergedColors = collect([
                ...($pivotColors ?? []),
                ...$colorsToAdd,
            ])
                ->unique(
                    fn ($color) =>
                    $this->normalizeHex($color)
                )
                ->values()
                ->all();

            /*
             * Update pivot.
             */
            $pivotData = [
                'colors' => $mergedColors,
            ];

            /*
             * Important:
             *
             * Template had [] positions,
             * so save the fallback positions
             * from MockupSideSetting.
             */
            if ($usedDefaultPositions) {
                $pivotData['positions'] =
                    $this->positionsToPivot($positions);
            }

            $mockup->templates()->updateExistingPivot(
                $template->id,
                $pivotData
            );

            /*
             * Generate only newly added colors.
             */
            foreach ($addedHexes as $hex) {

                foreach ($positions as $side => $points) {

                    if (
                        $this->alreadyGenerated(
                            $mockup,
                            $template->id,
                            $hex,
                            $side
                        )
                    ) {
                        continue;
                    }

                    $renderJobs[] = [
                        'template_id' => $template->id,
                        'hex' => $hex,
                        'side' => $side,
                        'points' => $points,
                    ];
                }
            }
        }

        if (empty($renderJobs)) {
            return;
        }

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'processing',
            'total_count' => count($renderJobs),
            'completed_count' => 0,
            'failed_count' => 0,
            'started_at' => now(),
        ]);

        foreach ($renderJobs as $job) {

            $item = BulkJobItem::create([
                'bulk_job_id' => $bulkJob->id,

                'template_id' =>
                    $job['template_id'],

                'color' =>
                    $hexToOriginalColor[$job['hex']]
                    ?? $job['hex'],

                'side' =>
                    $job['side'],

                'points' =>
                    $job['points'],

                'status' =>
                    'pending',
            ]);

            RenderMockupJob::dispatch(
                $bulkJob,
                $item,
                $mockup
            );
        }
    }

    /**
     * Get positions already stored
     * in mockup_template pivot.
     *
     * Stored format:
     *
     * [
     *   {
     *      "name": "front",
     *      "p1x": ...,
     *      ...
     *   }
     * ]
     *
     * Returned format:
     *
     * [
     *   "front" => [
     *      "p1x" => ...,
     *      ...
     *   ]
     * ]
     */
    protected function getTemplatePositions(
        $template
    ): array {
        $positions = $template->pivot->positions ?? [];

        if (is_string($positions)) {
            $positions = json_decode(
                $positions,
                true
            ) ?? [];
        }

        if (empty($positions)) {
            return [];
        }

        return collect($positions)
            ->filter(
                fn ($position) =>
                !empty($position['name'])
            )
            ->mapWithKeys(function ($position) {

                $side = $position['name'];

                unset($position['name']);

                return [
                    $side => $position,
                ];
            })
            ->all();
    }

    /**
     * Default positions from MockupSideSetting.
     *
     * IMPORTANT:
     * Keep coordinates exactly as stored.
     *
     * No * 1200 here.
     */
    protected function getDefaultPositions(
        Mockup $mockup
    ): array {
        return $mockup->sideSettings
            ->filter(
                fn ($setting) =>
                    $setting->is_active
                    && !empty($setting->warp_points)
            )
            ->mapWithKeys(function ($setting) {

                $points = $setting->warp_points;

                if (is_string($points)) {
                    $points = json_decode(
                        $points,
                        true
                    ) ?? [];
                }

                if (empty($points)) {
                    return [];
                }

                return [
                    $setting->side => [
                        'p1x' => (float) ($points['p1x'] ?? 0),
                        'p1y' => (float) ($points['p1y'] ?? 0),

                        'p2x' => (float) ($points['p2x'] ?? 0),
                        'p2y' => (float) ($points['p2y'] ?? 0),

                        'p3x' => (float) ($points['p3x'] ?? 0),
                        'p3y' => (float) ($points['p3y'] ?? 0),

                        'p4x' => (float) ($points['p4x'] ?? 0),
                        'p4y' => (float) ($points['p4y'] ?? 0),
                    ],
                ];
            })
            ->all();
    }

    /**
     * Convert:
     *
     * [
     *   'front' => [...]
     * ]
     *
     * to pivot format:
     *
     * [
     *   [
     *      'name' => 'front',
     *      ...
     *   ]
     * ]
     */
    protected function positionsToPivot(
        array $positions
    ): array {
        return collect($positions)
            ->map(function ($points, $side) {

                return [
                    'name' => $side,

                    'p1x' => $points['p1x'],
                    'p1y' => $points['p1y'],

                    'p2x' => $points['p2x'],
                    'p2y' => $points['p2y'],

                    'p3x' => $points['p3x'],
                    'p3y' => $points['p3y'],

                    'p4x' => $points['p4x'],
                    'p4y' => $points['p4y'],
                ];
            })
            ->values()
            ->all();
    }

    protected function alreadyGenerated(
        Mockup $mockup,
               $templateId,
        string $hex,
        string $side
    ): bool {
        return $mockup->media
            ->where(
                'collection_name',
                'generated_mockups'
            )
            ->contains(function ($media) use (
                $templateId,
                $hex,
                $side
            ) {
                return
                    (string) $media->getCustomProperty(
                        'template_id'
                    ) === (string) $templateId

                    &&

                    (string) $media->getCustomProperty(
                        'side'
                    ) === (string) $side

                    &&

                    $this->normalizeHex(
                        (string) $media->getCustomProperty(
                            'hex',
                            ''
                        )
                    ) === $hex;
            });
    }

    protected function normalizeHex(
        string $color
    ): string {
        return strtolower(
            ltrim(
                trim($color),
                '#'
            )
        );
    }

    public function deleted(
        Mockup $mockup
    ): void {
        $templateIds = $mockup
            ->templates
            ->pluck('id');

        Design::whereIn(
            'template_id',
            $templateIds
        )
            ->get()
            ->each(function ($design) {
                $design->clearMediaCollection();
                $design->forceDelete();
            });

        $mockup->clearMediaCollection();
    }
}
