<?php

namespace App\Services\Mockup;

use App\Jobs\RenderMockupJob;
use App\Models\BulkJobItem;
use App\Models\Design;
use App\Models\Mockup;
use App\Models\MockupGenerationJob;
use App\Models\Template;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TemplateMockupGenerator
{
    protected const STATIC_DEFAULT_POINTS = [
        0.35, 0.25,
        0.65, 0.25,
        0.35, 0.55,
        0.65, 0.55,
    ];
    public function generate(Mockup $mockup, array $colors = [], bool $force = false, ?Template $template = null): ?MockupGenerationJob
    {
        if ($template && empty($colors)) {
            $attachedTemplate = $mockup->templates()->where('templates.id', $template->id)->first();
            $colors = $attachedTemplate
                ? $this->toArray($attachedTemplate->pivot->colors ?? [])
                : ($mockup->colors_across_templates ?? []);
        } elseif (empty($colors)) {
            $colors = $mockup->colors_across_templates ?? [];
        }

        $colors = $this->cleanColors($colors);

        if ($template) {
            $this->attachTemplateIfMissing($mockup, $template, $colors);
        } elseif (!empty($colors)) {
            $this->attachMatchingTemplates($mockup, $colors);
        }

        if ($template) {
            if (!$mockup->templates()->where('templates.id', $template->id)->exists()) {
                return null;
            }
        } elseif (!$mockup->templates()->exists()) {
            return null;
        }

        if ($force) {
            if ($template) {
                $this->deleteGeneratedFilesForTemplate($mockup, $template->id);
            } else {
                $this->deleteGeneratedFiles($mockup);
            }
        }

        if (empty($colors)) {
            return $this->generateWithoutColor($mockup, $template?->id, $force);
        }

        $hexes = collect($colors)
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        return $this->generateForNewColors($mockup, $hexes, $template?->id);
    }
    public function generateForUnlinkedMockups(Template $template, array $excludeMockupIds = []): void
    {
        $hasProducts = $template->products()->exists();
        $hasCategories = $template->categories()->exists();

        if (!$hasProducts && !$hasCategories) {
            return;
        }

        $excludeMockupIds = collect($excludeMockupIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $productIdsQuery = $template->products()->select('products.id');
        $categoryIdsQuery = $template->categories()->select('categories.id');

        Mockup::query()
            ->where(function ($query) use ($hasProducts, $hasCategories, $productIdsQuery, $categoryIdsQuery) {
                if ($hasCategories) {
                    $query->whereIn('mockups.category_id', $categoryIdsQuery);
                }

                if ($hasProducts) {
                    $method = $hasCategories ? 'orWhereHas' : 'whereHas';
                    $query->{$method}('products', function ($productQuery) use ($productIdsQuery) {
                        $productQuery->whereIn('products.id', $productIdsQuery);
                    });
                }
            })
            ->whereDoesntHave('templates', function ($query) use ($template) {
                $query->where('templates.id', $template->id);
            })
            ->when(!empty($excludeMockupIds), function ($query) use ($excludeMockupIds) {
                $query->whereIntegerNotInRaw('mockups.id', $excludeMockupIds);
            })
            ->lazyById(100, 'mockups.id', 'id')
            ->each(function ($mockup) use ($template) {
                $colors = $mockup->colors_across_templates ?? [];

                if (empty($colors)) {
                    return;
                }

                $this->generate(mockup: $mockup, colors: $colors, template: $template);
            });
    }

    public function handleCreated(Mockup $mockup): void
    {
        $colors = $this->cleanColors($mockup->colors_across_templates ?? []);

        if (empty($colors)) {
            return;
        }

        $this->generate($mockup, $colors);
    }

    public function handleUpdated(Mockup $mockup, ?array $oldColors = null, ?Template $template = null): void
    {
        if ($oldColors === null && !$mockup->wasChanged('colors_across_templates')) {
            return;
        }

        $oldColors ??= $this->toArray($mockup->getOriginal('colors_across_templates'));

        $newColors = $this->toArray($mockup->colors_across_templates ?? []);

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

        $addedHexes = array_values(array_diff($newHexes, $oldHexes));
        $removedHexes = array_values(array_diff($oldHexes, $newHexes));

        if (!empty($removedHexes)) {
            $this->removeDeletedColors(
                mockup: $mockup,
                removedHexes: $removedHexes,
                template: $template
            );
        }

        if (!empty($addedHexes)) {
            if ($template) {
                $this->attachTemplateIfMissing(
                    $mockup,
                    $template,
                    $this->cleanColors($newColors)
                );

                $this->generateForNewColors(
                    $mockup,
                    $addedHexes,
                    $template->id
                );

                return;
            }

            $this->attachMatchingTemplates($mockup, $this->cleanColors($newColors));

            $this->generateForNewColors($mockup, $addedHexes);
        }
    }
    public function syncTemplateDefaults(Mockup $mockup, ?Collection $colors = null): void
    {
        $colors ??= collect($mockup->colors_across_templates ?? []);
        $colors = $this->cleanColors($colors->all());

        if (empty($colors)) {
            return;
        }

        $this->attachMatchingTemplates($mockup, $colors);

        if (!$mockup->templates()->exists()) {
            return;
        }

        $hexes = collect($colors)
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        $this->generateForNewColors($mockup, $hexes);
    }

    protected function attachTemplateIfMissing(Mockup $mockup, Template $template, array $colors): void
    {
        $alreadyAttached = $mockup->templates()->where('templates.id', $template->id)->exists();

        if ($alreadyAttached) {
            return;
        }

        $colors = $this->cleanColors($colors);

        $mockup->templates()->syncWithoutDetaching([
            $template->id => [
                'colors' => $colors,
                'positions' => [],
                'model_color' => $colors[0] ?? null,
            ],
        ]);
    }

    protected function attachMatchingTemplates(Mockup $mockup, array $colors): void
    {
        $colors = $this->cleanColors($colors);

        if (empty($colors)) {
            return;
        }

        $hasCategory = !empty($mockup->category_id);
        $hasProducts = $mockup->products()->exists();

        if (!$hasCategory && !$hasProducts) {
            return;
        }

        $productIdsQuery = $mockup->products()->select('products.id');

        Template::query()
            ->select('templates.id')
            ->where(function ($query) use ($mockup, $hasCategory, $hasProducts, $productIdsQuery) {
                if ($hasCategory) {
                    $query->whereHas('categories', function ($categoryQuery) use ($mockup) {
                        $categoryQuery->where('categories.id', $mockup->category_id);
                    });
                }

                if ($hasProducts) {
                    $method = $hasCategory ? 'orWhereHas' : 'whereHas';
                    $query->{$method}('products', function ($productQuery) use ($productIdsQuery) {
                        $productQuery->whereIn('products.id', $productIdsQuery);
                    });
                }
            })
            ->whereDoesntHave('mockups', function ($query) use ($mockup) {
                $query->where('mockups.id', $mockup->id);
            })
            ->lazyById(100, 'templates.id', 'id')
            ->chunk(100)
            ->each(function ($templates) use ($mockup, $colors) {
                $pivotData = [];
                $primaryColor = $colors[0] ?? null;

                foreach ($templates as $template) {
                    $pivotData[$template->id] = [
                        'colors' => $colors,
                        'positions' => [],
                        'model_color' => $primaryColor,
                    ];
                }

                if (empty($pivotData)) {
                    return;
                }

                $mockup->templates()->syncWithoutDetaching($pivotData);
            });
    }
    public function removeDeletedColors(Mockup $mockup, array $removedHexes, ?Template $template = null): void
    {
        $removedHexes = collect($removedHexes)
            ->filter()
            ->map(fn ($color) => $this->normalizeHex($color))
            ->unique()
            ->values()
            ->all();

        if (empty($removedHexes)) {
            return;
        }

        $templatesQuery = $mockup->templates();

        if ($template) {
            $templatesQuery->where('templates.id', $template->id);
        }

        $newModelColorsByTemplate = [];
        $templatesWithoutColors = [];

        $templatesQuery->lazyById(100, 'templates.id', 'id')
            ->each(function ($currentTemplate) use ($mockup, $removedHexes, &$newModelColorsByTemplate, &$templatesWithoutColors) {
                $pivotColors = $this->toArray($currentTemplate->pivot->colors ?? []);

                $remainingColors = collect($pivotColors)
                    ->filter()
                    ->reject(fn ($color) => in_array($this->normalizeHex($color), $removedHexes, true))
                    ->values()
                    ->all();

                $updateData = ['colors' => $remainingColors];
                $modelColor = $currentTemplate->pivot->model_color;

                if (!empty($modelColor) && in_array($this->normalizeHex($modelColor), $removedHexes, true)) {
                    $newModelColor = $remainingColors[0] ?? null;
                    $updateData['model_color'] = $newModelColor;

                    if ($newModelColor !== null) {
                        $newModelColorsByTemplate[(string) $currentTemplate->id] = $newModelColor;
                    }
                }

                if (empty($remainingColors)) {
                    $updateData['model_color'] = null;
                    $templatesWithoutColors[] = (string) $currentTemplate->id;
                }

                $mockup->templates()->updateExistingPivot($currentTemplate->id, $updateData);
            });

        $mediaQuery = $mockup->media()->where('collection_name', 'generated_mockups');

        if ($template) {
            $mediaQuery->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                [(string) $template->id]
            );
        }

        $mediaQuery
            ->where(function ($query) use ($removedHexes) {
                foreach ($removedHexes as $hex) {
                    $query->orWhereRaw(
                        "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                        [ltrim($hex, '#')]
                    );
                }
            })
            ->lazyById(100)
            ->each(fn ($media) => $media->delete());

        foreach ($newModelColorsByTemplate as $templateId => $color) {
            $this->forceGenerateColorForTemplate($mockup, $templateId, $color);
        }

        foreach (array_unique($templatesWithoutColors) as $templateId) {
            $this->generateWithoutColor($mockup, $templateId, true);
        }
    }
    protected function forceGenerateColorForTemplate(Mockup $mockup, string $templateId, string $color): void
    {
        $template = $mockup->templates()
            ->where('templates.id', $templateId)
            ->first();

        if (!$template) {
            return;
        }

        $pivotPositions = $this->toArray($template->pivot->positions ?? []);

        $positions = empty($pivotPositions)
            ? $this->getDefaultPositions($mockup, $template)
            : $this->getTemplatePositions($template);

        if (empty($positions)) {
            return;
        }

        $color = $this->formatHex($color);

        $this->deleteGeneratedColorForTemplate($mockup, (int) $templateId, $color);

        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?",
                [$templateId]
            )
            ->get()
            ->each(function ($media) {
                $media->setCustomProperty('model_image', 0);
                $media->save();
            });

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => count($positions),
            'completed_count' => 0,
            'failed_count' => 0,
            'started_at' => now(),
        ]);

        foreach ($positions as $side => $points) {
            $item = BulkJobItem::create([
                'bulk_job_id' => $bulkJob->id,
                'template_id' => $templateId,
                'color' => $color,
                'side' => $side,
                'points' => $points,
                'status' => 'pending',
            ]);

            RenderMockupJob::dispatch($bulkJob, $item, $mockup);
        }

        $bulkJob->update(['status' => 'processing']);
    }
    protected function normalizeHex(string $color): string
    {
        return strtolower(trim($color));
    }

    protected function formatHex(string $color): string
    {
        $color = strtolower(trim($color));

        return str_starts_with($color, '#') ? $color : '#' . $color;
    }

    protected function generateForNewColors(Mockup $mockup, array $addedHexes, ?string $templateId = null): ?MockupGenerationJob
    {
        $addedHexes = collect($addedHexes)
            ->filter()
            ->map(fn ($color) => $this->formatHex($color))
            ->unique()
            ->values()
            ->all();

        if (empty($addedHexes)) {
            return null;
        }

        $hexToOriginalColor = collect($mockup->colors_across_templates ?? [])
            ->filter()
            ->keyBy(fn ($color) => $this->normalizeHex($color))
            ->all();

        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
        ]);

        $totalCount = 0;

        $templatesQuery = $mockup->templates();

        if ($templateId) {
            $templatesQuery->where('templates.id', $templateId);
        }

        $templatesQuery
            ->lazyById(100, 'templates.id', 'id')
            ->each(function ($template) use ($mockup, $bulkJob, $addedHexes, $hexToOriginalColor, &$totalCount) {
                $pivotPositions = $this->toArray($template->pivot->positions ?? []);
                $isPivotPositionsEmpty = empty($pivotPositions);

                $positions = $isPivotPositionsEmpty
                    ? $this->getDefaultPositions($mockup, $template)
                    : $this->getTemplatePositions($template);

                if (empty($positions)) {
                    Log::warning('No valid positions found for template', [
                        'mockup_id' => $mockup->id,
                        'template_id' => $template->id,
                        'pivot_positions_empty' => $isPivotPositionsEmpty,
                    ]);

                    return;
                }

                $pivotColors = $this->toArray($template->pivot->colors ?? []);

                $colorsToAdd = collect($addedHexes)
                    ->map(fn ($hex) => $hexToOriginalColor[$hex] ?? $hex)
                    ->all();

                $mergedColors = collect([...$pivotColors, ...$colorsToAdd])
                    ->filter()
                    ->unique(fn ($color) => $this->normalizeHex($color))
                    ->values()
                    ->all();

                $pivotUpdateData = ['colors' => $mergedColors];

                if ($isPivotPositionsEmpty) {
                    $pivotUpdateData['positions'] = $this->positionsForPivot($positions);
                }

                $currentModelColor = $template->pivot->model_color ?? null;

                if (empty($currentModelColor) && !empty($mergedColors)) {
                    $pivotUpdateData['model_color'] = $mergedColors[0];
                }

                $mockup->templates()->updateExistingPivot($template->id, $pivotUpdateData);

                foreach ($addedHexes as $hex) {
                    foreach ($positions as $side => $points) {
                        if ($this->alreadyGenerated($mockup, $template->id, $hex, $side)) {
                            continue;
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

            return $bulkJob;
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
            ->each(fn ($item) => RenderMockupJob::dispatch($bulkJob, $item, $mockup));
        return $bulkJob;
    }
    protected function generateWithoutColor(Mockup $mockup, ?string $templateId = null, bool $force = false): void
    {
        $bulkJob = MockupGenerationJob::create([
            'mockup_id' => $mockup->id,
            'status' => 'pending',
            'total_count' => 0,
            'completed_count' => 0,
            'failed_count' => 0,
        ]);

        $totalCount = 0;
        $templatesQuery = $mockup->templates();

        if ($templateId) {
            $templatesQuery->where('templates.id', $templateId);
        }

        $templatesQuery
            ->lazyById(100, 'templates.id', 'id')
            ->each(function ($template) use ($mockup, $bulkJob, $force, &$totalCount) {
                $pivotColors = $this->cleanColors($this->toArray($template->pivot->colors ?? []));

                if (!empty($pivotColors)) {
                    return;
                }

                $pivotPositions = $this->toArray($template->pivot->positions ?? []);
                $isPivotPositionsEmpty = empty($pivotPositions);
                $positions = $isPivotPositionsEmpty
                    ? $this->getDefaultPositions($mockup, $template)
                    : $this->getTemplatePositions($template);

                if (empty($positions)) {
                    return;
                }

                $pivotUpdateData = ['colors' => [], 'model_color' => null];

                if ($isPivotPositionsEmpty) {
                    $pivotUpdateData['positions'] = $this->positionsForPivot($positions);
                }

                $mockup->templates()->updateExistingPivot($template->id, $pivotUpdateData);

                if ($force) {
                    $this->deleteGeneratedWithoutColorForTemplate($mockup, (int) $template->id);
                }

                foreach ($positions as $side => $points) {
                    if (!$force && $this->alreadyGeneratedWithoutColor($mockup, $template->id, $side)) {
                        continue;
                    }

                    BulkJobItem::create([
                        'bulk_job_id' => $bulkJob->id,
                        'template_id' => $template->id,
                        'color' => null,
                        'side' => $side,
                        'points' => $points,
                        'status' => 'pending',
                    ]);

                    $totalCount++;
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
            ->where('bulk_job_id', $bulkJob->id)
            ->where('status', 'pending')
            ->lazyById(100)
            ->each(fn ($item) => RenderMockupJob::dispatch($bulkJob, $item, $mockup));
    }

    protected function alreadyGeneratedWithoutColor(Mockup $mockup, $templateId, string $side): bool
    {
        return $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
            ->where(function ($query) {
                $query->whereNull('custom_properties->hex')
                    ->orWhereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '') = ''");
            })
            ->exists();
    }

    protected function deleteGeneratedWithoutColorForTemplate(Mockup $mockup, int $templateId): void
    {
        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
            ->where(function ($query) {
                $query->whereNull('custom_properties->hex')
                    ->orWhereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '') = ''");
            })
            ->lazyById(100)
            ->each(fn ($media) => $media->delete());
    }

    protected function deleteGeneratedColorForTemplate(Mockup $mockup, int $templateId, string $hex): void
    {
        $normalizedHex = ltrim($this->normalizeHex($hex), '#');

        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
            ->whereRaw(
                "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                [$normalizedHex]
            )
            ->lazyById(100)
            ->each(fn ($media) => $media->delete());
    }

    protected function getTemplatePositions(Template $template): array
    {
        $positions = $this->toArray($template->pivot->positions ?? []);

        if (empty($positions)) {
            return [];
        }

        return collect($positions)
            ->filter(fn ($position) => is_array($position) && !empty($position['name']) && $this->hasValidWarpPoints($position))
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

    protected function getDefaultPositions(Mockup $mockup, Template $template): array
    {
        $positions = $mockup->sideSettings()
            ->where('is_active', true)
            ->whereNotNull('warp_points')
            ->cursor()
            ->reduce(function (array $positions, $setting) {
                $points = $this->toArray($setting->warp_points);

                if (!$this->hasValidWarpPoints($points)) {
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
            }, []);

        if (!empty($positions)) {
            return $positions;
        }

        [$designWidth, $designHeight] = $this->getTemplateDesignDimensions($template);

        $sides = $mockup->types
            ->map(fn ($type) => $type->value?->key())
            ->filter()
            ->unique()
            ->values();

        if ($sides->isEmpty()) {
            $sides = collect(['front']);
        }

        return $sides
            ->mapWithKeys(fn ($side) => [$side => $this->computeDefaultPoints($designWidth, $designHeight)])
            ->all();
    }

    protected function computeDefaultPoints(float $designWidth, float $designHeight): array
    {
        [$tlX, $tlY, $trX, $trY, $blX, $blY, $brX, $brY] = self::STATIC_DEFAULT_POINTS;

        $minX = min($tlX, $trX, $blX, $brX);
        $maxX = max($tlX, $trX, $blX, $brX);
        $minY = min($tlY, $trY, $blY, $brY);
        $maxY = max($tlY, $trY, $blY, $brY);

        $boxWidth = $maxX - $minX;
        $boxHeight = $maxY - $minY;
        $centerX = ($minX + $maxX) / 2;
        $centerY = ($minY + $maxY) / 2;

        $safeDesignWidth = max(1, $designWidth);
        $safeDesignHeight = max(1, $designHeight);

        $designAspect = $safeDesignWidth / $safeDesignHeight;
        $boxAspect = $boxWidth / max($boxHeight, 0.000001);

        $finalWidth = $boxWidth;
        $finalHeight = $boxHeight;

        if ($designAspect > $boxAspect) {
            $finalHeight = $boxWidth / $designAspect;
        } else {
            $finalWidth = $boxHeight * $designAspect;
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

    protected function getTemplateDesignDimensions(Template $template): array
    {
        $pairs = [
            ['design_width', 'design_height'],
            ['image_width', 'image_height'],
            ['source_width', 'source_height'],
            ['width', 'height'],
        ];

        foreach ($pairs as [$widthKey, $heightKey]) {
            $width = $template->getAttribute($widthKey);
            $height = $template->getAttribute($heightKey);

            if (is_numeric($width) && is_numeric($height) && (float) $width > 0 && (float) $height > 0) {
                return [(float) $width, (float) $height];
            }
        }

        $media = $template->getFirstMedia('templates') ?? $template->getFirstMedia('templates-preview');

        if ($media) {
            $width = $media->getCustomProperty('width');
            $height = $media->getCustomProperty('height');

            if (is_numeric($width) && is_numeric($height) && (float) $width > 0 && (float) $height > 0) {
                return [(float) $width, (float) $height];
            }
        }

        return [1.0, 1.0];
    }

    protected function decimalString(float $value): string
    {
        return rtrim(rtrim(number_format($value, 17, '.', ''), '0'), '.');
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
        $required = ['p1x', 'p1y', 'p2x', 'p2y', 'p3x', 'p3y', 'p4x', 'p4y'];

        foreach ($required as $key) {
            if (!array_key_exists($key, $points) || !is_numeric($points[$key])) {
                return false;
            }
        }

        return true;
    }
    protected function alreadyGenerated(Mockup $mockup, $templateId, string $hex, string $side): bool
    {
        return $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.side')) = ?", [$side])
            ->whereRaw(
                "LOWER(REPLACE(JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.hex')), '#', '')) = ?",
                [ltrim($this->normalizeHex($hex), '#')]
            )
            ->exists();
    }

    public function handleDeleted(Mockup $mockup): void
    {
        $mockup->templates()
            ->select('templates.id')
            ->lazyById(100, 'templates.id', 'id')
            ->each(function ($template) {
                Design::query()
                    ->where('template_id', $template->id)
                    ->lazyById(100)
                    ->each(function ($design) {
                        $design->clearMediaCollection();
                        $design->forceDelete();
                    });
            });

        $mockup->clearMediaCollection();
    }

    protected function cleanColors(array $colors): array
    {
        return collect($colors)
            ->filter(fn ($color) => is_string($color) && trim($color) !== '')
            ->map(fn ($color) => $this->formatHex($color))
            ->unique(fn ($color) => $this->normalizeHex($color))
            ->values()
            ->all();
    }

    protected function toArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return [];
    }

    protected function deleteGeneratedFiles(Mockup $mockup): void
    {
        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->lazyById(100)
            ->each(fn ($media) => $media->delete());
    }

    protected function deleteGeneratedFilesForTemplate(Mockup $mockup, int $templateId): void
    {
        $mockup->media()
            ->where('collection_name', 'generated_mockups')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_properties, '$.template_id')) = ?", [(string) $templateId])
            ->lazyById(100)
            ->each(fn ($media) => $media->delete());
    }
}
