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

        $oldColors = $this->toArray(
            $mockup->getOriginal('pre_fill_colors')
        );

        $newColors = $mockup->pre_fill_colors ?? [];

        $oldHexes = collect($oldColors)
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->all();

        $newHexes = collect($newColors)
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
        /*
         * Get default positions without loading
         * sideSettings relation into Mockup.
         */
        $defaultPositions = $this->getDefaultPositions($mockup);

        $hexToOriginalColor = collect(
            $mockup->pre_fill_colors ?? []
        )
            ->keyBy(
                fn ($color) => $this->normalizeHex($color)
            )
            ->all();

        /*
         * Create job first as pending.
         *
         * We don't dispatch anything until all BulkJobItems
         * are created and total_count is known.
         */
        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
        ]);

        $totalCount = 0;

        /*
         * Do NOT:
         *
         * $mockup->load('templates')
         *
         * Process templates in chunks instead.
         */
        $mockup->templates()
            ->orderBy('templates.id')
            ->lazy(100)
            ->each(function ($template) use (
                $mockup,
                $bulkJob,
                $addedHexes,
                $defaultPositions,
                $hexToOriginalColor,
                &$totalCount
            ) {
                /*
                 * 1. Template custom position.
                 */
                $positions = $this->getTemplatePositions(
                    $template
                );

                /*
                 * 2. No custom position?
                 *
                 * Use sideSetting defaults.
                 *
                 * IMPORTANT:
                 * We DON'T save these into pivot.positions.
                 *
                 * positions = [] still means:
                 * "use mockup defaults".
                 */
                if (empty($positions)) {
                    $positions = $defaultPositions;
                }

                /*
                 * No custom position + no default position.
                 */
                if (empty($positions)) {
                    return;
                }

                /*
                 * Update template colors only.
                 */
                $pivotColors = $this->toArray(
                    $template->pivot->colors ?? []
                );

                $colorsToAdd = collect($addedHexes)
                    ->map(
                        fn ($hex) =>
                            $hexToOriginalColor[$hex] ?? $hex
                    )
                    ->all();

                $mergedColors = collect([
                    ...$pivotColors,
                    ...$colorsToAdd,
                ])
                    ->filter()
                    ->unique(
                        fn ($color) =>
                        $this->normalizeHex($color)
                    )
                    ->values()
                    ->all();

                $mockup->templates()
                    ->updateExistingPivot(
                        $template->id,
                        [
                            'colors' => $mergedColors,

                            // Do NOT write positions here.
                        ]
                    );

                /*
                 * Create BulkJobItems directly.
                 *
                 * No big $renderJobs array in memory.
                 */
                collect($addedHexes)
                    ->each(function ($hex) use (
                        $positions,
                        $mockup,
                        $template,
                        $bulkJob,
                        $hexToOriginalColor,
                        &$totalCount
                    ) {
                        collect($positions)
                            ->each(function ($points, $side) use (
                                $mockup,
                                $template,
                                $bulkJob,
                                $hex,
                                $hexToOriginalColor,
                                &$totalCount
                            ) {
                                if (
                                    $this->alreadyGenerated(
                                        $mockup,
                                        $template->id,
                                        $hex,
                                        $side
                                    )
                                ) {
                                    return;
                                }

                                BulkJobItem::create([
                                    'bulk_job_id' => $bulkJob->id,

                                    'template_id' =>
                                        $template->id,

                                    'color' =>
                                        $hexToOriginalColor[$hex]
                                        ?? $hex,

                                    'side' =>
                                        $side,

                                    'points' =>
                                        $points,

                                    'status' =>
                                        'pending',
                                ]);

                                $totalCount++;
                            });
                    });
            });

        /*
         * Nothing needs rendering.
         */
        if ($totalCount === 0) {
            $bulkJob->update([
                'status' => 'completed',
                'total_count' => 0,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return;
        }

        /*
         * Set total BEFORE dispatching jobs.
         *
         * This prevents jobs finishing before
         * total_count is correctly known.
         */
        $bulkJob->update([
            'status' => 'processing',
            'total_count' => $totalCount,
            'started_at' => now(),
        ]);

        /*
         * Dispatch items in chunks.
         *
         * Don't load all BulkJobItems into memory.
         */
        BulkJobItem::query()
            ->where('bulk_job_id', $bulkJob->id)
            ->where('status', 'pending')
            ->lazyById(100)
            ->each(
                fn ($item) =>
                RenderMockupJob::dispatch(
                    $bulkJob,
                    $item,
                    $mockup
                )
            );
    }

    /**
     * Get custom template positions.
     *
     * [] means template doesn't override
     * the MockupSideSetting position.
     */
    protected function getTemplatePositions(
        $template
    ): array {
        $positions = $this->toArray(
            $template->pivot->positions ?? []
        );

        if (empty($positions)) {
            return [];
        }

        return collect($positions)
            ->filter(
                fn ($position) =>
                    is_array($position)
                    && !empty($position['name'])
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
     * Get MockupSideSetting defaults without:
     *
     * $mockup->load('sideSettings')
     *
     * warp_points are kept exactly in their
     * warp_points are kept exactly in their
     * stored coordinate system.
     */
    protected function getDefaultPositions(
        Mockup $mockup
    ): array {
        return $mockup->sideSettings()
            ->where('is_active', true)
            ->whereNotNull('warp_points')
            ->cursor()
            ->reduce(
                function (
                    array $positions,
                          $setting
                ) {
                    $points = $this->toArray(
                        $setting->warp_points
                    );

                    if (empty($points)) {
                        return $positions;
                    }

                    $positions[$setting->side] = [
                        'p1x' => (float) ($points['p1x'] ?? 0),
                        'p1y' => (float) ($points['p1y'] ?? 0),

                        'p2x' => (float) ($points['p2x'] ?? 0),
                        'p2y' => (float) ($points['p2y'] ?? 0),

                        'p3x' => (float) ($points['p3x'] ?? 0),
                        'p3y' => (float) ($points['p3y'] ?? 0),

                        'p4x' => (float) ($points['p4x'] ?? 0),
                        'p4y' => (float => (float) ($points['p4x'] ?? 0),
                        'p4y' => (float) ($points['p4y'] ?? 0),
                    ];

                    return $positions;
                },
                []
            );
    }

    /**
     * Check database directly.
     *
     * Don't load:
     *
     * $mockup->media
     *
     * into memory.
     */
    protected function alreadyGenerated(
        Mockup $mockup,
               $templateId,
        string $hex,
        string $side
    ): bool {
        return $mockup->media()
            ->where(
                'collection_name',
                'generated_mockups'
            )
            ->whereRaw(
                "JSON_UNQUOTE(
                    JSON_EXTRACT(
                        custom_properties,
                        '$.template_id'
                    )
                ) = ?",
                [(string) $templateId]
            )
            ->whereRaw(
                "JSON_UNQUOTE(
                    JSON_EXTRACT(
                        custom_properties,
                        '$.side'
                    )
                ) = ?",
                [$side]
            )
            ->whereRaw(
                "LOWER(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            custom_properties,
                            '$.hex'
                        )
                    )
                ) = ?",
                [$hex]
            )
            ->exists();
    }

    protected function toArray(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return)) {
                return $value;
            }

        if (is_string($value)) {
            return json_decode(
                $value,
                true
            ) ?? [];
        }

        return [];
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
                ->templates()
                ->pluck('templates.id');

            Design::query()
                ->whereIn(
                    'template_id',
                    $templateIds
                )
                ->lazyById(100)
                ->each(function ($design) {
                    $design->clearMediaCollection();
                    $design->forceDelete();
                });

            $mockup->clearMediaCollection();
        }
    }
