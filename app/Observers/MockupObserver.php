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
            ->all();

        $newHexes = collect($mockup->pre_fill_colors ?? [])
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->all();

        $addedHexes = array_values(
            array_diff($newHexes, $oldHexes)
        );

        if (empty($addedHexes)) {
            return;
        }

        $this->generateForNewColors($mockup, $addedHexes);
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

        $hexToOriginalColor = collect($mockup->pre_fill_colors ?? [])
            ->keyBy(fn ($color) => $this->normalizeHex($color))
            ->all();

        $renderJobs = [];

        foreach ($mockup->templates as $template) {

            /*
             * Priority:
             *
             * 1. Template pivot positions
             * 2. Mockup side settings
             * 3. Skip
             */
            $positions = $this->getTemplatePositions($template);

            if (empty($positions)) {
                $positions = $this->getDefaultPositions($mockup);
            }

            if (empty($positions)) {
                continue;
            }

            /*
             * Add new colors to template pivot.
             */
            $pivotColors = $template->pivot->colors ?? [];

            if (is_string($pivotColors)) {
                $pivotColors = json_decode($pivotColors, true) ?? [];
            }

            $colorsToAdd = collect($addedHexes)
                ->map(fn ($hex) => $hexToOriginalColor[$hex] ?? $hex)
                ->all();

            $mergedColors = collect([
                ...($pivotColors ?? []),
                ...$colorsToAdd,
            ])
                ->unique(fn ($color) => $this->normalizeHex($color))
                ->values()
                ->all();

            $mockup->templates()->updateExistingPivot(
                $template->id,
                [
                    'colors' => $mergedColors,
                ]
            );

            /*
             * Create render jobs.
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
                'template_id' => $job['template_id'],
                'color' => $hexToOriginalColor[$job['hex']] ?? $job['hex'],
                'side' => $job['side'],
                'points' => $job['points'],
                'status' => 'pending',
            ]);

            RenderMockupJob::dispatch(
                $bulkJob,
                $item,
                $mockup
            );
        }
    }

    /**
     * Get custom positions from mockup_template pivot.
     */
    protected function getTemplatePositions($template): array
    {
        $positions = $template->pivot->positions ?? [];

        if (is_string($positions)) {
            $positions = json_decode($positions, true) ?? [];
        }

        if (empty($positions)) {
            return [];
        }

        /*
         * Format:
         *
         * [
         *     [
         *         "name" => "front",
         *         "p1x" => ...,
         *         ...
         *     ]
         * ]
         */
        return collect($positions)
            ->filter(fn ($position) => !empty($position['name']))
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
     * Use MockupSideSetting as default positions.
     */
    protected function getDefaultPositions(Mockup $mockup): array
    {
        return $mockup->sideSettings
            ->filter(
                fn ($setting) =>
                    $setting->is_active
                    && !empty($setting->warp_points)
            )
            ->mapWithKeys(
                fn ($setting) => [
                    $setting->side => $setting->warp_points,
                ]
            )
            ->all();
    }

    /**
     * Prevent generating duplicate image.
     */
    protected function alreadyGenerated(
        Mockup $mockup,
         $templateId,
        string $hex,
        string $side
    ): bool {
        return $mockup->media
            ->where('collection_name', 'generated_mockups')
            ->contains(function ($media) use (
                $templateId,
                $hex,
                $side
            ) {
                return
                    (string) $media->getCustomProperty('template_id')
                    === (string) $templateId

                    && (string) $media->getCustomProperty('side')
                    === $side

                    && $this->normalizeHex(
                        $media->getCustomProperty('hex', '')
                    ) === $hex;
            });
    }

    protected function normalizeHex(string $color): string
    {
        return strtolower(
            ltrim(
                trim($color),
                '#'
            )
        );
    }

    public function deleted(Mockup $mockup): void
    {
        $templateIds = $mockup->templates
            ->pluck('id');

        Design::whereIn('template_id', $templateIds)
            ->get()
            ->each(function ($design) {
                $design->clearMediaCollection();
                $design->forceDelete();
            });

        $mockup->clearMediaCollection();
    }
}
