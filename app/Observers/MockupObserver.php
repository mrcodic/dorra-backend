<?php

namespace App\Observers;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Design;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use App\Models\Template;
use Illuminate\Support\Facades\Log;

class MockupObserver
{
    protected const STATIC_DEFAULT_POINTS = [
        0.35, 0.25,
        0.65, 0.25,
        0.35, 0.55,
        0.65, 0.55,
    ];

    public function created(Mockup $mockup): void
    {
        $colors = collect($mockup->pre_fill_colors ?? [])->filter()->values();

        if ($colors->isEmpty()) {
            return;
        }

        $this->syncTemplateDefaults($mockup, $colors);
    }


    public function syncTemplateDefaults(Mockup $mockup, ?\Illuminate\Support\Collection $colors = null): void
    {
        $colors ??= collect($mockup->pre_fill_colors ?? [])->filter()->values();

        if ($colors->isEmpty()) {
            return;
        }

        $templates = $this->getAttachableTemplates($mockup);

        if ($templates->isEmpty()) {
            return;
        }

        $alreadyAttachedIds = $mockup->templates()->pluck('templates.id')->all();

        $templatesToAttach = $templates->reject(
            fn ($template) => in_array($template->id, $alreadyAttachedIds, true)
        );

        if ($templatesToAttach->isNotEmpty()) {
            $mockup->templates()->attach(
                $templatesToAttach->pluck('id')->all(),
                [
                    'colors' => [],
                    'positions' => [],
                ]
            );
        }

        $hexes = $colors
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        $this->generateForNewColors($mockup, $hexes);
    }

    protected function getAttachableTemplates(Mockup $mockup)
    {
        $productIds = $mockup->products()->pluck('products.id');

        if (!$mockup->category_id && $productIds->isEmpty()) {
            return collect();
        }

        return Template::query()
            ->where(function ($query) use ($mockup, $productIds) {
                if ($mockup->category_id) {
                    $query->orWhereHas(
                        'categories',
                        fn ($q) => $q->where('categories.id', $mockup->category_id)
                    );
                }

                if ($productIds->isNotEmpty()) {
                    $query->orWhereHas(
                        'products',
                        fn ($q) => $q->whereIn('products.id', $productIds)
                    );
                }
            })
            ->get();
    }

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
                $pivotPositions = $this->toArray(
                    $template->pivot->positions ?? []
                );

                $isPivotPositionsEmpty = empty($pivotPositions);

                if ($isPivotPositionsEmpty) {
                    $positions = $defaultPositions;
                } else {
                    $positions = $this->getTemplatePositions($template);
                }

                if (empty($positions)) {
                    Log::warning('No valid positions found for template', [
                        'mockup_id' => $mockup->id,
                        'template_id' => $template->id,
                        'pivot_positions_empty' => $isPivotPositionsEmpty,
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
                if ($isPivotPositionsEmpty) {
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
                        'p1x' => (string) $position['p1x'],
                        'p1y' => (string) $position['p1y'],
                        'p2x' => (string) $position['p2x'],
                        'p2y' => (string) $position['p2y'],
                        'p3x' => (string) $position['p3x'],
                        'p3y' => (string) $position['p3y'],
                        'p4x' => (string) $position['p4x'],
                        'p4y' => (string) $position['p4y'],
                    ],
                ];
            })
            ->all();
    }

    protected function getDefaultPositions(Mockup $mockup): array
    {
        $positions = $mockup->sideSettings()
            ->where('is_active', true)
            ->whereNotNull('warp_points')
            ->get()
            ->reduce(
                function (array $positions, $setting) {
                    $points = $this->toArray(
                        $setting->warp_points
                    );

                    if (!$this->hasValidWarpPoints($points)) {
                        Log::warning('Invalid default warp points', [
                            'side_setting_id' => $setting->id ?? null,
                            'side' => $setting->side ?? null,
                            'warp_points' => $points,
                        ]);

                        return $positions;
                    }

                    $positions[$setting->side] = [
                        'p1x' => (string) $points['p1x'],
                        'p1y' => (string) $points['p1y'],

                        'p2x' => (string) $points['p2x'],
                        'p2y' => (string) $points['p2y'],

                        'p3x' => (string) $points['p4x'],
                        'p3y' => (string) $points['p4y'],
                        'p4x' => (string) $points['p3x'],
                        'p4y' => (string) $points['p3y'],
                    ];

                    return $positions;
                },
                []
            );

        if (!empty($positions)) {
            return $positions;
        }

        $sides = $mockup->types
            ->map(fn ($type) => $type->value?->key())
            ->filter()
            ->unique()
            ->values();

        if ($sides->isEmpty()) {
            $sides = collect(['front']);
        }

        return $sides
            ->mapWithKeys(
                fn ($side) => [$side => $this->staticDefaultPositions()]
            )
            ->all();
    }
    protected function staticDefaultPositions(): array
    {
        [$p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y] = self::STATIC_DEFAULT_POINTS;

        return [
            'p1x' => (string) $p1x,
            'p1y' => (string) $p1y,
            'p2x' => (string) $p2x,
            'p2y' => (string) $p2y,
            'p3x' => (string) $p3x,
            'p3y' => (string) $p3y,
            'p4x' => (string) $p4x,
            'p4y' => (string) $p4y,
        ];
    }

    protected function positionsForPivot(array $positions): array
    {
        return collect($positions)
            ->map(function ($points, $side) {
                if (!$this->hasValidWarpPoints($points)) {
                    return null;
                }

                return [
                    'name' => $side,

                    'p1x' => (string) $points['p1x'],
                    'p1y' => (string) $points['p1y'],
                    'p2x' => (string) $points['p2x'],
                    'p2y' => (string) $points['p2y'],
                    'p3x' => (string) $points['p3x'],
                    'p3y' => (string) $points['p3y'],
                    'p4x' => (string) $points['p4x'],
                    'p4y' => (string) $points['p4y'],
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
