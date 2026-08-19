<?php

namespace App\Services\Mockup;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use App\Models\Template;

class TemplateMockupGenerator
{
    public function generate(
        Mockup $mockup,
        array $colors
    ): void {
        $colors = collect($colors)
            ->filter()
            ->map(
                fn ($color) =>
                $this->normalizeHex($color)
            )
            ->unique()
            ->values()
            ->all();

        if (empty($colors)) {
            return;
        }

        $this->attachMatchingTemplates(
            $mockup,
            $colors
        );

        if (!$mockup->templates()->exists()) {
            return;
        }

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
        ]);

        $totalCount = 0;

        $mockup
            ->templates()
            ->lazyById(
                100,
                'templates.id',
                'id'
            )
            ->each(function ($template) use (
                $mockup,
                $colors,
                $bulkJob,
                &$totalCount
            ) {
                $positions = $this->getPositions(
                    $mockup,
                    $template
                );

                if (empty($positions)) {
                    return;
                }

                $pivotColors = $this->toArray(
                    $template->pivot->colors ?? []
                );

                $mergedColors = collect([
                    ...$pivotColors,
                    ...$colors,
                ])
                    ->filter()
                    ->unique(
                        fn ($color) =>
                        $this->normalizeHex($color)
                    )
                    ->values()
                    ->all();

                $mockup
                    ->templates()
                    ->updateExistingPivot(
                        $template->id,
                        [
                            'colors' => $mergedColors,
                        ]
                    );

                foreach ($colors as $hex) {
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

                        BulkJobItem::create([
                            'bulk_job_id' =>
                                $bulkJob->id,

                            'template_id' =>
                                $template->id,

                            'color' =>
                                $hex,

                            'side' =>
                                $side,

                            'points' =>
                                $points,

                            'status' =>
                                'pending',
                        ]);

                        $totalCount++;
                    }
                }
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
                fn ($item) =>
                RenderMockupJob::dispatch(
                    $bulkJob,
                    $item,
                    $mockup
                )
            );
    }

    protected function getPositions(
        Mockup $mockup,
        Template $template
    ): array {
        $attachedTemplate = $mockup
            ->templates()
            ->where(
                'templates.id',
                $template->id
            )
            ->first();

        if (!$attachedTemplate) {
            return [];
        }

        $pivotPositions = $this->toArray(
            $attachedTemplate->pivot->positions ?? []
        );

        if (!empty($pivotPositions)) {
            return collect($pivotPositions)
                ->filter(
                    fn ($position) =>
                        is_array($position)
                        && !empty($position['name'])
                )
                ->mapWithKeys(function ($position) {
                    $side = $position['name'];

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

        return $mockup
            ->sideSettings()
            ->where(
                'is_active',
                true
            )
            ->whereNotNull(
                'warp_points'
            )
            ->get()
            ->mapWithKeys(function ($setting) {
                $points = $this->toArray(
                    $setting->warp_points
                );

                if (empty($points)) {
                    return [];
                }

                return [
                    $setting->side => [
                        'p1x' => (string) ($points['p1x'] ?? ''),
                        'p1y' => (string) ($points['p1y'] ?? ''),
                        'p2x' => (string) ($points['p2x'] ?? ''),
                        'p2y' => (string) ($points['p2y'] ?? ''),
                        'p3x' => (string) ($points['p3x'] ?? ''),
                        'p3y' => (string) ($points['p3y'] ?? ''),
                        'p4x' => (string) ($points['p4x'] ?? ''),
                        'p4y' => (string) ($points['p4y'] ?? ''),
                    ],
                ];
            })
            ->all();
    }

    protected function alreadyGenerated(
        Mockup $mockup,
               $templateId,
        string $hex,
        string $side
    ): bool {
        return $mockup
            ->media()
            ->where(
                'collection_name',
                'generated_mockups'
            )
            ->whereRaw(
                "
                JSON_UNQUOTE(
                    JSON_EXTRACT(
                        custom_properties,
                        '$.template_id'
                    )
                ) = ?
                ",
                [
                    (string) $templateId,
                ]
            )
            ->whereRaw(
                "
                JSON_UNQUOTE(
                    JSON_EXTRACT(
                        custom_properties,
                        '$.side'
                    )
                ) = ?
                ",
                [
                    $side,
                ]
            )
            ->whereRaw(
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
                [
                    $this->normalizeHex(
                        $hex
                    ),
                ]
            )
            ->exists();
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

    protected function toArray(
        mixed $value
    ): array {
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
    protected function attachMatchingTemplates(
        Mockup $mockup,
        array $colors
    ): void {
        Template::query()
            ->select('templates.id')
            ->where(function ($query) use ($mockup) {
                if ($mockup->category_id) {
                    $query->whereHas(
                        'categories',
                        function ($categoryQuery) use (
                            $mockup
                        ) {
                            $categoryQuery->where(
                                'categories.id',
                                $mockup->category_id
                            );
                        }
                    );
                }

                $query->orWhereHas(
                    'products',
                    function ($productQuery) use (
                        $mockup
                    ) {
                        $productQuery->whereIn(
                            'products.id',
                            $mockup
                                ->products()
                                ->select(
                                    'products.id'
                                )
                        );
                    }
                );
            })
            ->whereDoesntHave(
                'mockups',
                function ($query) use ($mockup) {
                    $query->where(
                        'mockups.id',
                        $mockup->id
                    );
                }
            )
            ->lazyById(
                100,
                'templates.id',
                'id'
            )
            ->chunk(100)
            ->each(function ($templates) use (
                $mockup,
                $colors
            ) {
                $pivotData = [];

                foreach ($templates as $template) {
                    $pivotData[$template->id] = [
                        'colors' => $colors,
                        'positions' => [],
                    ];
                }

                if (empty($pivotData)) {
                    return;
                }

                $mockup
                    ->templates()
                    ->syncWithoutDetaching(
                        $pivotData
                    );
            });
    }
}
