<?php

namespace App\Observers;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Design;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use Illuminate\Support\Facades\Log;

class MockupObserver
{
    public function updated(Mockup $mockup): void
    {
        if ($mockup->id == 234) {
            if (!$mockup->wasChanged('pre_fill_colors')) {
                return;
            }

            $oldColors = $this->toArray(
                $mockup->getOriginal('pre_fill_colors')
            );

            $newColors = $mockup->pre_fill_colors ?? [];

            $oldHexes = collect($oldColors)
                ->filter()
                ->map(fn($color) => $this->normalizeHex($color))
                ->unique()
                ->values()
                ->all();

            $newHexes = collect($newColors)
                ->filter()
                ->map(fn($color) => $this->normalizeHex($color))
                ->unique()
                ->values()
                ->all();

            $addedHexes = array_values(
                array_diff($newHexes, $oldHexes)
            );

            $removedHexes = array_values(
                array_diff($oldHexes, $newHexes)
            );

            if (!empty($removedHexes)) {
                $this->removeDeletedColors(
                    $mockup,
                    $removedHexes
                );
            }

            if (!empty($addedHexes)) {
                $this->generateForNewColors(
                    $mockup,
                    $addedHexes
                );
            }
        }
    }

    protected function removeDeletedColors(
        Mockup $mockup,
        array $removedHexes
    ): void {
        $removedHexes = collect($removedHexes)
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        if (empty($removedHexes)) {
            return;
        }

        $mockup->templates()
            ->orderBy('templates.id')
            ->lazy(100)
            ->each(function ($template) use (
                $mockup,
                $removedHexes
            ) {
                $pivot = $template->pivot;

                $pivotColors = $this->toArray(
                    $pivot->colors ?? []
                );

                $remainingColors = collect($pivotColors)
                    ->filter()
                    ->reject(function ($color) use ($removedHexes) {
                        return in_array(
                            $this->normalizeHex($color),
                            $removedHexes,
                            true
                        );
                    })
                    ->values()
                    ->all();

                $updateData = [
                    'colors' => $remainingColors,
                ];

                $modelColor = $pivot->model_color;

                if (
                    !empty($modelColor)
                    && in_array(
                        $this->normalizeHex($modelColor),
                        $removedHexes,
                        true
                    )
                ) {
                    $updateData['model_color'] = $remainingColors[0] ?? null;
                }

                $mockup->templates()
                    ->updateExistingPivot(
                        $template->id,
                        $updateData
                    );
            });

        $mockup->media()
            ->where(
                'collection_name',
                'generated_mockups'
            )
            ->where(function ($query) use ($removedHexes) {
                foreach ($removedHexes as $hex) {
                    $query->orWhereRaw(
                        "
                        LOWER(
                            REPLACE(
                                JSON_UNQUOTE(
                                    JSON_EXTRACT(
                                        custom_properties,
                                        '$.hex'
                                    )
                                ),
                                '#',
                                ''
                            )
                        ) = ?
                        ",
                        [$hex]
                    );
                }
            })
            ->lazyById(100)
            ->each(function ($media) {
                $media->delete();
            });
    }

    protected function generateForNewColors(
        Mockup $mockup,
        array $addedHexes
    ): void {
        /*
         * Job format:
         *
         * [
         *     'front' => [p1x, p1y, ...],
         *     'back'  => [p1x, p1y, ...],
         * ]
         */
        $defaultPositions = $this->getDefaultPositions($mockup);

        Log::info('Mockup default positions', [
            'mockup_id' => $mockup->id,
            'positions' => $defaultPositions,
        ]);

        $hexToOriginalColor = collect(
            $mockup->pre_fill_colors ?? []
        )
            ->keyBy(
                fn ($color) => $this->normalizeHex($color)
            )
            ->all();

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
        ]);

        $totalCount = 0;

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
                 |--------------------------------------------------------------------------
                 | Resolve positions
                 |--------------------------------------------------------------------------
                 |
                 | If this template already has positions on mockup_template, use them.
                 | Otherwise use the mockup side-settings defaults and persist those
                 | defaults into mockup_template.positions.
                 |
                 */

                $positions = $this->getTemplatePositions($template);

                $isUsingDefaultPositions = empty($positions);

                if ($isUsingDefaultPositions) {
                    $positions = $defaultPositions;
                }

                if (empty($positions)) {
                    Log::warning('No valid positions found for template', [
                        'mockup_id' => $mockup->id,
                        'template_id' => $template->id,
                    ]);

                    return;
                }

                /*
                 |--------------------------------------------------------------------------
                 | Resolve colors
                 |--------------------------------------------------------------------------
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

                /*
                 |--------------------------------------------------------------------------
                 | Update pivot
                 |--------------------------------------------------------------------------
                 */

                $pivotUpdateData = [
                    'colors' => $mergedColors,
                ];

                /*
                 * Persist defaults in the pivot only when the template does not
                 * already have its own custom positions.
                 *
                 * Pivot format:
                 *
                 * [
                 *     [
                 *         'name' => 'front',
                 *         'p1x' => ...,
                 *         ...
                 *     ]
                 * ]
                 */
                if ($isUsingDefaultPositions) {
                    $pivotUpdateData['positions'] =
                        $this->positionsForPivot($positions);
                }

                $mockup->templates()
                    ->updateExistingPivot(
                        $template->id,
                        $pivotUpdateData
                    );

                /*
                 |--------------------------------------------------------------------------
                 | Create bulk job items
                 |--------------------------------------------------------------------------
                 |
                 | IMPORTANT:
                 | We use the exact same $positions variable that was saved
                 | into the pivot when defaults were used.
                 |
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

                                Log::info('Creating mockup render item', [
                                    'mockup_id' => $mockup->id,
                                    'template_id' => $template->id,
                                    'color' => $hex,
                                    'side' => $side,
                                    'points' => $points,
                                ]);

                                BulkJobItem::create([
                                    'bulk_job_id' => $bulkJob->id,
                                    'template_id' => $template->id,
                                    'color' => $hexToOriginalColor[$hex] ?? $hex,
                                    'side' => $side,
                                    'points' => $points,
                                    'status' => 'pending',
                                ]);

                                $totalCount++;
                            });
                    });
            });

        if ($totalCount === 0) {
            $bulkJob->update([
                'status' => 'completed',
                'total_count' => 0,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            return;
        }

        $bulkJob->update([
            'status' => 'processing',
            'total_count' => $totalCount,
            'started_at' => now(),
        ]);

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
     * Convert stored pivot positions into the map format used by jobs.
     *
     * Pivot:
     * [
     *     [
     *         'name' => 'front',
     *         'p1x' => ...,
     *         ...
     *     ]
     * ]
     *
     * Returns:
     * [
     *     'front' => [
     *         'p1x' => ...,
     *         ...
     *     ]
     * ]
     */
    protected function getTemplatePositions($template): array
    {
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
                    && $this->hasValidWarpPoints($position)
            )
            ->mapWithKeys(function ($position) {
                $side = (string) $position['name'];

                return [
                    $side => [
                        'p1x' => (float) $position['p1x'],
                        'p1y' => (float) $position['p1y'],
                        'p2x' => (float) $position['p2x'],
                        'p2y' => (float) $position['p2y'],
                        'p3x' => (float) $position['p3x'],
                        'p3y' => (float) $position['p3y'],
                        'p4x' => (float) $position['p4x'],
                        'p4y' => (float) $position['p4y'],
                    ],
                ];
            })
            ->all();
    }

    /**
     * Get default active positions from mockup side settings.
     *
     * This returns the exact format consumed by BulkJobItem:
     *
     * [
     *     'front' => [...points],
     *     'back'  => [...points],
     * ]
     */
    protected function getDefaultPositions(Mockup $mockup): array
    {
        return $mockup->sideSettings()
            ->where('is_active', true)
            ->whereNotNull('warp_points')
            ->get()
            ->reduce(
                function (array $positions, $setting) {
                    $points = $this->toArray(
                        $setting->warp_points
                    );

                    /*
                     * Never silently replace a missing point with 0.
                     * An incomplete warp point set can create a completely wrong
                     * render position.
                     */
                    if (!$this->hasValidWarpPoints($points)) {
                        Log::warning('Invalid default warp points', [
                            'side_setting_id' => $setting->id ?? null,
                            'side' => $setting->side ?? null,
                            'warp_points' => $points,
                        ]);

                        return $positions;
                    }

                    $positions[$setting->side] = [
                        'p1x' => (float) $points['p1x'],
                        'p1y' => (float) $points['p1y'],
                        'p2x' => (float) $points['p2x'],
                        'p2y' => (float) $points['p2y'],
                        'p3x' => (float) $points['p3x'],
                        'p3y' => (float) $points['p3y'],
                        'p4x' => (float) $points['p4x'],
                        'p4y' => (float) $points['p4y'],
                    ];

                    return $positions;
                },
                []
            );
    }

    /**
     * Convert the map/job position format into mockup_template.positions format.
     */
    protected function positionsForPivot(array $positions): array
    {
        return collect($positions)
            ->map(function ($points, $side) {
                if (!$this->hasValidWarpPoints($points)) {
                    return null;
                }

                return [
                    'name' => $side,
                    'p1x' => (float) $points['p1x'],
                    'p1y' => (float) $points['p1y'],
                    'p2x' => (float) $points['p2x'],
                    'p2y' => (float) $points['p2y'],
                    'p3x' => (float) $points['p3x'],
                    'p3y' => (float) $points['p3y'],
                    'p4x' => (float) $points['p4x'],
                    'p4y' => (float) $points['p4y'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function hasValidWarpPoints(array $points): bool
    {
        $required = [
            'p1x',
            'p1y',
            'p2x',
            'p2y',
            'p3x',
            'p3y',
            'p4x',
            'p4y',
        ];

        foreach ($required as $key) {
            if (!array_key_exists($key, $points)) {
                return false;
            }

            if (!is_numeric($points[$key])) {
                return false;
            }
        }

        return true;
    }

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
                    REPLACE(
                        JSON_UNQUOTE(
                            JSON_EXTRACT(
                                custom_properties,
                                '$.hex'
                            )
                        ),
                        '#',
                        ''
                    )
                ) = ?",
                [$this->normalizeHex($hex)]
            )
            ->exists();
    }

    protected function toArray(mixed $value): array
    {
        if (is_array($value)) {
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
