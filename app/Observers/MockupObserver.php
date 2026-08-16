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
        $colors = collect($mockup->pre_fill_colors ?? [])
            ->filter()
            ->values();

        if ($colors->isEmpty()) {
            return;
        }

        $this->syncTemplateDefaults($mockup, $colors);
    }

    public function syncTemplateDefaults(
        Mockup                          $mockup,
        ?\Illuminate\Support\Collection $colors = null
    ): void
    {
        $colors ??= collect($mockup->pre_fill_colors ?? [])
            ->filter()
            ->values();

        if ($colors->isEmpty()) {
            return;
        }

        $templatesQuery = $this->getAttachableTemplates($mockup);

        if (!$templatesQuery) {
            return;
        }

        $templatesQuery
            ->whereDoesntHave(
                'mockups',
                fn($query) => $query->where(
                    'mockups.id',
                    $mockup->id
                )
            )
            ->orderBy('templates.id')
            ->lazy(100)
            ->chunk(100)
            ->each(function ($templates) use ($mockup) {
                $ids = $templates
                    ->pluck('id')
                    ->all();

                if (empty($ids)) {
                    return;
                }

                $mockup->templates()->attach(
                    $ids,
                    [
                        'colors' => [],
                        'positions' => [],
                    ]
                );
            });

        $hexes = $colors
            ->map(
                fn($color) => $this->normalizeHex($color)
            )
            ->unique()
            ->values()
            ->all();

        $this->generateForNewColors(
            $mockup,
            $hexes
        );
    }

    protected function getAttachableTemplates(
        Mockup $mockup
    ): ?\Illuminate\Database\Eloquent\Builder
    {
        $productIds = $mockup
            ->products()
            ->pluck('products.id');

        if (
            !$mockup->category_id
            && $productIds->isEmpty()
        ) {
            return null;
        }

        return Template::query()
            ->where(function ($query) use (
                $mockup,
                $productIds
            ) {
                if ($mockup->category_id) {
                    $query->orWhereHas(
                        'categories',
                        fn($q) => $q->where(
                            'categories.id',
                            $mockup->category_id
                        )
                    );
                }

                if ($productIds->isNotEmpty()) {
                    $query->orWhereHas(
                        'products',
                        fn($q) => $q->whereIn(
                            'products.id',
                            $productIds
                        )
                    );
                }
            });
    }

    public function updated(Mockup $mockup): void
    {
        if ($mockup->id == 235) {
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
        array  $removedHexes
    ): void
    {
        $removedHexes = collect($removedHexes)
            ->filter()
            ->map(fn($color) => $this->normalizeHex($color))
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
                    $updateData['model_color'] =
                        $remainingColors[0] ?? null;
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
        array  $addedHexes
    ): void
    {
        $hexToOriginalColor = collect(
            $mockup->pre_fill_colors ?? []
        )
            ->keyBy(
                fn($color) => $this->normalizeHex($color)
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
                $hexToOriginalColor,
                &$totalCount
            ) {
                $pivotPositions = $this->toArray(
                    $template->pivot->positions ?? []
                );

                $isPivotPositionsEmpty = empty($pivotPositions);

                if ($isPivotPositionsEmpty) {
                    $positions = $this->getDefaultPositions(
                        $mockup,
                        $template
                    );
                } else {
                    $positions = $this->getTemplatePositions(
                        $template
                    );
                }

                if (empty($positions)) {
                    Log::warning(
                        'No valid positions found for template',
                        [
                            'mockup_id' => $mockup->id,
                            'template_id' => $template->id,
                            'pivot_positions_empty' => $isPivotPositionsEmpty,
                        ]
                    );

                    return;
                }

                $pivotColors = $this->toArray(
                    $template->pivot->colors ?? []
                );

                $colorsToAdd = collect($addedHexes)
                    ->map(
                        fn($hex) => $hexToOriginalColor[$hex] ?? $hex
                    )
                    ->all();

                $mergedColors = collect([
                    ...$pivotColors,
                    ...$colorsToAdd,
                ])
                    ->filter()
                    ->unique(
                        fn($color) => $this->normalizeHex($color)
                    )
                    ->values()
                    ->all();

                $pivotUpdateData = [
                    'colors' => $mergedColors,
                ];

                if ($isPivotPositionsEmpty) {
                    $pivotUpdateData['positions'] =
                        $this->positionsForPivot(
                            $positions
                        );
                }

                $mockup->templates()
                    ->updateExistingPivot(
                        $template->id,
                        $pivotUpdateData
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
                            ->each(function (
                                $points,
                                $side
                            ) use (
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

                                Log::info(
                                    'Creating mockup render item',
                                    [
                                        'mockup_id' => $mockup->id,
                                        'template_id' => $template->id,
                                        'color' => $hex,
                                        'side' => $side,
                                        'points' => $points,
                                    ]
                                );

                                BulkJobItem::create([
                                    'bulk_job_id' => $bulkJob->id,
                                    'template_id' => $template->id,
                                    'color' =>
                                        $hexToOriginalColor[$hex] ?? $hex,
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
            ->where(
                'bulk_job_id',
                $bulkJob->id
            )
            ->where(
                'status',
                'pending'
            )
            ->lazyById(100)
            ->each(
                fn($item) => RenderMockupJob::dispatch(
                    $bulkJob,
                    $item,
                    $mockup
                )
            );
    }

    protected function getTemplatePositions(
        $template
    ): array
    {
        $positions = $this->toArray(
            $template->pivot->positions ?? []
        );

        if (empty($positions)) {
            return [];
        }

        return collect($positions)
            ->filter(
                fn($position) => is_array($position)
                    && !empty($position['name'])
                    && $this->hasValidWarpPoints(
                        $position
                    )
            )
            ->mapWithKeys(function ($position) {
                $side = (string)$position['name'];

                return [
                    $side => [
                        'p1x' => (string)$position['p1x'],
                        'p1y' => (string)$position['p1y'],
                        'p2x' => (string)$position['p2x'],
                        'p2y' => (string)$position['p2y'],
                        'p3x' => (string)$position['p3x'],
                        'p3y' => (string)$position['p3y'],
                        'p4x' => (string)$position['p4x'],
                        'p4y' => (string)$position['p4y'],
                    ],
                ];
            })
            ->all();
    }

    protected function getDefaultPositions(
        Mockup   $mockup,
        Template $template
    ): array
    {
        $positions = $mockup
            ->sideSettings()
            ->where(
                'is_active',
                true
            )
            ->whereNotNull(
                'warp_points'
            )
            ->get()
            ->reduce(
                function (
                    array $positions,
                          $setting
                ) {
                    $points = $this->toArray(
                        $setting->warp_points
                    );

                    if (
                        !$this->hasValidWarpPoints(
                            $points
                        )
                    ) {
                        return $positions;
                    }

                    $positions[$setting->side] = [
                        'p1x' => (string)$points['p1x'],
                        'p1y' => (string)$points['p1y'],
                        'p2x' => (string)$points['p2x'],
                        'p2y' => (string)$points['p2y'],
                        'p3x' => (string)$points['p4x'],
                        'p3y' => (string)$points['p4y'],
                        'p4x' => (string)$points['p3x'],
                        'p4y' => (string)$points['p3y'],
                    ];

                    return $positions;
                },
                []
            );

        if (!empty($positions)) {
            return $positions;
        }

        $sides = $mockup->types
            ->map(
                fn($type) => $type->value?->key()
            )
            ->filter()
            ->unique()
            ->values();

        if ($sides->isEmpty()) {
            $sides = collect(['front']);
        }

        return $sides
            ->mapWithKeys(function ($side) use (
                $template
            ) {
                [$designWidth, $designHeight] =
                    $this->getTemplateDesignDimensions(
                        $template,
                        $side
                    );

                return [
                    $side =>
                        $this->computeDefaultPoints(
                            $designWidth,
                            $designHeight
                        ),
                ];
            })
            ->all();
    }

    protected function computeDefaultPoints(
        float $designWidth,
        float $designHeight
    ): array
    {
        [
            $tlX,
            $tlY,
            $trX,
            $trY,
            $blX,
            $blY,
            $brX,
            $brY,
        ] = self::STATIC_DEFAULT_POINTS;

        $minX = min(
            $tlX,
            $trX,
            $blX,
            $brX
        );

        $maxX = max(
            $tlX,
            $trX,
            $blX,
            $brX
        );

        $minY = min(
            $tlY,
            $trY,
            $blY,
            $brY
        );

        $maxY = max(
            $tlY,
            $trY,
            $blY,
            $brY
        );

        $boxWidth = $maxX - $minX;
        $boxHeight = $maxY - $minY;

        $centerX = ($minX + $maxX) / 2;
        $centerY = ($minY + $maxY) / 2;

        $safeDesignWidth = max(
            1,
            $designWidth
        );

        $safeDesignHeight = max(
            1,
            $designHeight
        );

        $designAspect =
            $safeDesignWidth
            / $safeDesignHeight;

        $boxAspect =
            $boxWidth
            / max(
                $boxHeight,
                0.000001
            );

        $finalWidth = $boxWidth;
        $finalHeight = $boxHeight;

        if ($designAspect > $boxAspect) {
            $finalHeight =
                $boxWidth
                / $designAspect;
        } else {
            $finalWidth =
                $boxHeight
                * $designAspect;
        }

        $halfWidth = $finalWidth / 2;
        $halfHeight = $finalHeight / 2;

        $newTlX = $centerX - $halfWidth;
        $newTlY = $centerY - $halfHeight;
        $newTrX = $centerX + $halfWidth;
        $newTrY = $centerY - $halfHeight;
        $newBlX = $centerX - $halfWidth;
        $newBlY = $centerY + $halfHeight;
        $newBrX = $centerX + $halfWidth;
        $newBrY = $centerY + $halfHeight;

        return [
            'p1x' => $this->decimalString($newTlX),
            'p1y' => $this->decimalString($newTlY),
            'p2x' => $this->decimalString($newTrX),
            'p2y' => $this->decimalString($newTrY),
            'p3x' => $this->decimalString($newBlX),
            'p3y' => $this->decimalString($newBlY),
            'p4x' => $this->decimalString($newBrX),
            'p4y' => $this->decimalString($newBrY),
        ];
    }

    protected function getTemplateDesignDimensions(
        Template $template,
        string   $side = 'front'
    ): array
    {
        $side = strtolower($side);

        $isWithoutEditor =
            $template->approach === 'without_editor';

        $collection = match ($side) {
            'back' =>
            $template->use_front_as_back
                ? (
            $isWithoutEditor
                ? 'templates-preview'
                : 'templates'
            )
                : (
            $isWithoutEditor
                ? 'back-templates-preview'
                : 'back_templates'
            ),

            default =>
            $isWithoutEditor
                ? 'templates-preview'
                : 'templates',
        };

        $media = $template
            ->getFirstMedia($collection);

        if (!$media) {
            return [1.0, 1.0];
        }

        $width = $media
            ->getCustomProperty('width');

        $height = $media
            ->getCustomProperty('height');

        if (
            !is_numeric($width)
            || !is_numeric($height)
            || (float)$width <= 0
            || (float)$height <= 0
        ) {
            return [1.0, 1.0];
        }

        return [
            (float)$width,
            (float)$height,
        ];
    }

    protected function decimalString(
        float $value
    ): string
    {
        return rtrim(
            rtrim(
                number_format(
                    $value,
                    17,
                    '.',
                    ''
                ),
                '0'
            ),
            '.'
        );
    }

    protected function positionsForPivot(
        array $positions
    ): array
    {
        return collect($positions)
            ->map(function (
                $points,
                $side
            ) {
                if (
                    !$this->hasValidWarpPoints(
                        $points
                    )
                ) {
                    return null;
                }

                return [
                    'name' => $side,
                    'p1x' => (string)$points['p1x'],
                    'p1y' => (string)$points['p1y'],
                    'p2x' => (string)$points['p2x'],
                    'p2y' => (string)$points['p2y'],
                    'p3x' => (string)$points['p3x'],
                    'p3y' => (string)$points['p3y'],
                    'p4x' => (string)$points['p4x'],
                    'p4y' => (string)$points['p4y'],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function hasValidWarpPoints(
        array $points
    ): bool
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
            if (
                !array_key_exists(
                    $key,
                    $points
                )
            ) {
                return false;
            }

            if (
                !is_numeric(
                    $points[$key]
                )
            ) {
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
    ): bool
    {
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
                [(string)$templateId]
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
                [
                    $this->normalizeHex(
                        $hex
                    ),
                ]
            )
            ->exists();
    }

    protected function toArray(
        mixed $value
    ): array
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

    protected function normalizeHex(
        string $color
    ): string
    {
        return strtolower(
            ltrim(
                trim($color),
                '#'
            )
        );
    }

    public function deleted(
        Mockup $mockup
    ): void
    {
        $templateIds = $mockup
            ->templates()
            ->pluck(
                'templates.id'
            );

        Design::query()
            ->whereIn(
                'template_id',
                $templateIds
            )
            ->lazyById(100)
            ->each(function ($design) {
                $design
                    ->clearMediaCollection();

                $design
                    ->forceDelete();
            });

        $mockup
            ->clearMediaCollection();
    }

    public function syncTemplateForMockup(
        Mockup   $mockup,
        Template $template
    ): void
    {
        $colors = collect(
            $mockup->pre_fill_colors ?? []
        )
            ->filter()
            ->unique(
                fn($color) => $this->normalizeHex($color)
            )
            ->values()
            ->all();

        $pivot = $mockup
            ->templates()
            ->where(
                'templates.id',
                $template->id
            )
            ->first()?->pivot;

        if (!$pivot) {
            return;
        }

        $pivotPositions = $this->toArray(
            $pivot->positions ?? []
        );

        if (empty($pivotPositions)) {
            $positions = $this->getDefaultPositions(
                $mockup,
                $template
            );
        } else {
            $positions = $this->getTemplatePositions(
                $mockup
                    ->templates()
                    ->where(
                        'templates.id',
                        $template->id
                    )
                    ->first()
            );
        }

        $mockup
            ->templates()
            ->updateExistingPivot(
                $template->id,
                [
                    'colors' => $colors,

                    'positions' =>
                        empty($pivotPositions)
                            ? $this->positionsForPivot(
                            $positions
                        )
                            : $pivotPositions,
                ]
            );
    }
}
