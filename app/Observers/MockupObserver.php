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
          if ($mockup->id == 234)
        {
        if (!$mockup->
        wasChanged('pre_fill_colors')) {
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
            ->values()
            ->all();

        $newHexes = collect($newColors)
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        /*
         * Colors newly added to the mockup.
         */
        $addedHexes = array_values(
            array_diff($newHexes, $oldHexes)
        );

        /*
         * Colors removed from the mockup.
         */
        $removedHexes = array_values(
            array_diff($oldHexes, $newHexes)
        );

        /*
         * First clean removed colors.
         */
        if (!empty($removedHexes)) {
            $this->removeDeletedColors(
                $mockup,
                $removedHexes
            );
        }

        /*
         * Then generate newly added colors.
         */
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

        /*
         |--------------------------------------------------------------------------
         | 1. Remove colors from mockup_template pivot
         |--------------------------------------------------------------------------
         |
         | For every template:
         |
         | - remove deleted colors from pivot.colors
         | - if model_color is one of the deleted colors:
         |      use the first remaining color as model_color
         | - if there are no remaining colors:
         |      model_color becomes null
         |
         */

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

                /*
                 * Check if the removed color is currently
                 * being used as model_color.
                 */
                $modelColor = $pivot->model_color;

                if (
                    !empty($modelColor)
                    && in_array(
                        $this->normalizeHex($modelColor),
                        $removedHexes,
                        true
                    )
                ) {
                    /*
                     * Apply another available color.
                     *
                     * Example:
                     *
                     * colors:
                     * [
                     *     "#ff0000",
                     *     "#000000",
                     *     "#ffffff"
                     * ]
                     *
                     * model_color = "#ff0000"
                     *
                     * remove "#ff0000"
                     *
                     * result:
                     * colors = [
                     *     "#000000",
                     *     "#ffffff"
                     * ]
                     *
                     * model_color = "#000000"
                     */

                    $updateData['model_color']
                        = $remainingColors[0] ?? null;
                }

                $mockup->templates()
                    ->updateExistingPivot(
                        $template->id,
                        $updateData
                    );
            });

        /*
         |--------------------------------------------------------------------------
         | 2. Delete generated media/files for removed colors
         |--------------------------------------------------------------------------
         |
         | Spatie Media Library ->delete() removes:
         |
         | - media database row
         | - physical generated file
         | - conversions related to that media
         |
         */

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
        $defaultPositions = $this->getDefaultPositions($mockup);

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
                $positions = $this->getTemplatePositions($template);

                if (empty($positions)) {
                    $positions = $defaultPositions;
                }

                if (empty($positions)) {
                    return;
                }

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
                        ]
                    );

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

    protected function getDefaultPositions(Mockup $mockup): array
    {
        return $mockup->sideSettings()
            ->where('is_active', true)
            ->whereNotNull('warp_points')
            ->cursor()
            ->reduce(
                function (array $positions, $setting) {
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
                        'p4y' => (float) ($points['p4y'] ?? 0),
                    ];

                    return $positions;
                },
                []
            );
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
