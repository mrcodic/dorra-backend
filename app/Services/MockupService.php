<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository, public MockupRenderer $renderer)
    {
        parent::__construct($repository);
    }

    public function getMockups(): array
    {
        $productId  = request('product_id');
        $templateId = request('template_id');
        $color      = request('color');

        /**
         * Fetch mockups
         */
        $mockups = $this->repository
            ->query()
            ->when($productId, fn($q) => $q->whereCategoryId($productId))
            ->when($templateId, fn($q) => $q->whereHas('templates', function ($query) use ($templateId) {
                $query->where('templates.id', $templateId);
            }))
            ->with([
                'templates:id',
                'types:id,value',
                'media' => fn($q) => $q->whereIn('collection_name', [
                    'mockups',
                    'generated_mockups',
                    'templates',
                    'back_templates'
                ])
            ])
            ->get();


        $colors = $mockups
            ->pluck('templates.pivot.colors')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $urls = $mockups->each(function ($mockup) use ($colors) {
            $mockup->media
                ->where('collection_name', 'generated_mockups')
                ->map(fn($media) => $media->getFullUrl())
                ->values()
                ->all();

        });




        /**
         * Remove duplicates
         */
        $result = [
            'colors' => $colors,
            'urls'   => array_values(array_unique($urls)),
        ];

        return $result;
    }


    public function getAll(
        $relations = [], bool $paginate = false, $columns = ['*'], $perPage = 16, $counts = [])
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $query = $this->repository
            ->query()
            ->with(['category:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $q->where('name', 'LIKE', '%' . request('search_value') . '%');
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('color'), function ($query) {

            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereCategoryId(request('product_id')))
            ->when(request()->filled('template_id'), fn($q) => $q->whereHas('templates', function ($query) {
                $query->where('templates.id', request('template_id'));
            }))
            ->when(request()->filled('product_ids'), fn($q) => $q->whereIn('category_id', request()->array('product_ids')))
            ->when(request()->filled('type'), fn($q) => $q->whereHas('types', fn($q) => $q->where('types.id', request('type'))))
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }
        if (request()->expectsJson()) {
            return $query->paginate($pageSize);
        }
        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters: $this->filters,
            perPage: $pageSize ?? $perPage
        );
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData) {

            $model = $this->repository->create($validatedData);

            $model->types()->attach(Arr::get($validatedData, 'types') ?? []);

            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {

                collect($validatedData['templates'])->each(function ($template) use ($model) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;

                    $positions = collect($template)
                        ->except(['template_id', 'colors'])
                        ->filter(fn ($value) => !is_null($value))
                        ->toArray();

                    $colors = Arr::get($template, 'colors', []);


                    // ✅ sanitize colors
                    $colors = collect($colors)
                        ->filter(fn ($c) => is_string($c) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c))
                        ->values()
                        ->all();

                    // ✅ avoid duplicates on pivot
                    $model->templates()->syncWithoutDetaching([
                        $templateId => [
                            'positions' => $positions,
                            'colors'    => $colors,
                        ],
                    ]);
                });

                // مهم عشان pivot values تبقى موجودة
                $model->load(['templates', 'types', 'category']);

                $this->handleFiles($model);

                foreach ($model->templates as $template) {

                    $pivotPositions = $template->pivot->positions ?? [];
                    $pivotColors    = $template->pivot->colors ?? [];

                    // pivot colors could be json string in some setups
                    if (is_string($pivotColors)) {
                        $pivotColors = json_decode($pivotColors, true) ?: [];
                    }
                    if (!is_array($pivotColors)) $pivotColors = [];

                    // لو مفيش ألوان مختارة: إمّا تعمل نسخة واحدة بدون لون أو تتخطى
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

                        $designMedia = $type == TypeEnum::BACK
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

            return $model;
        });

        return $model;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($id, $validatedData, $relationsToLoad) {

            $model = $this->repository->update($validatedData, $id);
            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $modelTypesValues = $model->types->pluck('value.value')->toArray();
            if (collect($selectedTypeValues)->sort()->values()->all() != collect($modelTypesValues)->sort()->values()->all()) {
                $model->templates()->detach();
            }
            $model->types()->sync(Arr::get($validatedData, 'types') ?? []);
            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {

                $syncData = [];

                $templatesInput->each(function ($template) use (&$syncData) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) {
                        return;
                    }

                    $positions = collect($template)
                        ->except('template_id')
                        ->filter(fn ($value) => !is_null($value) && $value !== '')
                        ->toArray();

                    $syncData[$templateId] = [
                        'positions' => $positions,
                    ];
                });


                if (!empty($syncData)) {
                    $model->templates()->sync($syncData);
                } else {
                    $model->templates()->detach();
                }

            }
            foreach ($model->templates as $template) {
                $pivotPositions = $template->pivot->positions ?? [];

                collect($model->types)->each(function ($type) use ($model, $template, $pivotPositions) {
                    $sideName = strtolower($type->value->name);

                    // ----- base & mask media -----
                    $baseMedia = $model->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'base'
                        );

                    $maskMedia = $model->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'mask'
                        );

                    if (!$baseMedia || !$maskMedia) {
                        return [$sideName => null];
                    }

                    $designMedia = $type == TypeEnum::BACK
                        ? $template->getFirstMedia('back_templates')
                        : $template->getFirstMedia('templates');

                    if (!$designMedia || !$designMedia->getPath()) {
                        throw new \Exception("Missing design media for {$sideName}");
                    }
                    $basePath = $baseMedia->getPath();
                    [$baseWidth, $baseHeight] = getimagesize($basePath);

                    // القيم جاية من الـ JS كنِسَب 0..1 من مساحة الموكاب
                    $xPct  = (float)($pivotPositions[$sideName . '_x']      ?? 0.5);  // مركز X
                    $yPct  = (float)($pivotPositions[$sideName . '_y']      ?? 0.5);  // مركز Y
                    $wPct  = (float)($pivotPositions[$sideName . '_width']  ?? 0.4);  // نسبة عرض البوكس
                    $hPct  = (float)($pivotPositions[$sideName . '_height'] ?? 0.4);  // نسبة ارتفاع البوكس
                    $angle = (float)($pivotPositions[$sideName . '_angle']  ?? 0);

                    // نحول النِّسَب لأبعاد فعلية
                    $printW = max(1, (int) round($wPct * $baseWidth));
                    $printH = max(1, (int) round($hPct * $baseHeight));

                    // مركز البوكس بالبيكسل
                    $centerX = $xPct * $baseWidth;
                    $centerY = $yPct * $baseHeight;

                    // نحسب الـ top-left من المركز
                    $printX = (int) round($centerX - $printW / 2);
                    $printY = (int) round($centerY - $printH / 2);

                    if ($printW <= 0) $printW = (int) round($baseWidth * 0.3);
                    if ($printH <= 0) $printH = (int) round($baseHeight * 0.3);

                    $firstMockup = $this->repository
                        ->query()
                        ->whereNotNull('colors')
                        ->whereBelongsTo($model->category)
                        ->first();

                    $binary = (new MockupRenderer())->render([
                        'base_path' => $basePath,
                        'shirt_path' => $maskMedia->getPath(),
                        'design_path' => $designMedia->getPath(),
                        'print_x' => $printX,
                        'print_y' => $printY,
                        'print_w' => $printW,
                        'print_h' => $printH,
                        'angle' => $angle ?? 0,
                        'hex' => $firstMockup?->colors ?
                            $firstMockup?->colors[0] : null,
                    ]);


                    $model
                        ->addMediaFromString($binary)
                        ->usingFileName("mockup_{$sideName}.png")
                        ->withCustomProperties([
                            'side' => $sideName,
                            'template_id' => $template->id,
                        ])
                        ->toMediaCollection('generated_mockups');
                });
            }

           if (request()->allFiles())
           {
               $this->handleFiles($model,true);
               $model->clearMediaCollection('generated_mockups');

           }


            return $model;
        });

        return $model;
    }

    /**
     * @param mixed $model
     * @return mixed
     */
    public function handleFiles(mixed $model, $clearExisting = false): mixed
    {
        if (!request()->allFiles()) {
            return $model;
        }

        $types = collect(request()->input('types', []));
        $mediaTypes = collect(['base_image', 'mask_image']);

        $types->each(function ($type) use ($mediaTypes, $model, $clearExisting) {
            $sideName = strtolower(TypeEnum::from($type)->name);

            $mediaTypes->each(function ($mediaType) use ($sideName, $type, $model, $clearExisting) {
                $inputName = "{$sideName}_{$mediaType}";

                if (!request()->hasFile($inputName)) {
                    return;
                }

                $role = str_contains($mediaType, 'base') ? 'base' : 'mask';

                if ($clearExisting) {
                    $model->media()
                        ->where('collection_name', 'mockups')
                        ->where('custom_properties->side', $sideName)
                        ->where('custom_properties->role', $role)
                        ->delete();
                }
                handleMediaUploads(
                    request()->file($inputName),
                    $model,
                    customProperties: [
                        'side' => $sideName,
                        'role' => $role,
                    ],

                );
            });
        });

        return $model;
    }

    public function deleteResource($id)
    {
        $model = $this->repository->find($id);
        if ($model->hasMedia()) {
            clearMediaCollections($model);
        }
        $model->types()->newPivotStatement()
            ->where('typeable_id', (string)$model->id)
            ->where('typeable_type', get_class($model))
            ->delete();

        return $model->delete();
    }

    public function bulkDeleteResources($ids)
    {
        return $this->handleTransaction(function () use ($ids) {
            $models = $this->repository->query()->whereIn('id', $ids)->get();

            $models->each(function ($model) {

                $model->types()->detach();

                if ($model->hasMedia()) {
                    clearMediaCollections($model);
                }
            });
            return $this->repository->query()->whereIn('id', $ids)->delete();
        });


    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->repository->find($id);
        return $mockup->load('types');
//        return auth('web')->user()->recentMockups()->syncWithoutDetaching([$mockup->id]);
    }

    public function destroyRecentMockup($id)
    {
        return auth('web')->user()->recentMockups()->detach($id);
    }

    public function recentMockups()
    {
        return auth('web')->user()->recentMockups()->take(5)->get();
    }
}
