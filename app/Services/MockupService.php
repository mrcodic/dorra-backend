<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository, public MockupRenderer $renderer)
    {
        parent::__construct($repository);
    }

    public function getMockups(): array
    {
        $mockups = $this->repository
            ->query()
            ->when(request()->filled('product_id'), fn($q) => $q->whereCategoryId(request('product_id')))
            ->when(request()->filled('template_id'), fn($q) => $q->whereHas('templates', function ($query) {
                $query->where('templates.id', request('template_id'));
            }))
            ->with(['templates', 'types', 'media'])
            ->get();


        $colors = $mockups->pluck('colors')
            ->filter()
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        $urls = [];
        $color = request('color');

        foreach ($mockups as $mockup) {
            foreach ($mockup->templates as $template) {
                foreach ($mockup->types as $type) {
                    $sideName = strtolower($type->value->name);

                    $baseMedia = $mockup->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'base'
                        );

                    $maskMedia = $mockup->getMedia('mockups')
                        ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'mask'
                        );

                    if (!$baseMedia || !$maskMedia) {
                        continue;
                    }

                    $designMedia = $type == TypeEnum::BACK
                        ? $template->getFirstMedia('back_templates')
                        : $template->getFirstMedia('templates');

                    if (!$designMedia || !$designMedia->getPath()) {
                        continue;
                    }


                    $existingMedia = $mockup->getMedia('generated_mockups')
                        ->first(function ($m) use ($sideName, $template, $color) {
                            return $m->getCustomProperty('side') === $sideName
                                && $m->getCustomProperty('template_id') === $template->id
                                && $m->getCustomProperty('color') === $color;
                        });

                    if ($existingMedia) {
                        $urls[] = $existingMedia->getFullUrl();
                        continue;
                    }


                    $binary = $this->renderer->render([
                        'base_path' => $baseMedia->getPath(),
                        'shirt_path' => $maskMedia->getPath(),
                        'design_path' => $designMedia->getPath(),
                        'hex' => $color,
                    ]);

                    $media = $mockup
                        ->addMediaFromString($binary)
                        ->usingFileName("mockup_{$sideName}_{$template->id}_{$color}.png")
                        ->withCustomProperties([
                            'side' => $sideName,
                            'template_id' => $template->id,
                            'color' => $color,
                        ])
                        ->toMediaCollection('generated_mockups');

                    $urls[] = $media->getFullUrl();
                }
            }
        }


        $urls = array_values(array_unique($urls));

        return [
            'colors' => $colors,
            'urls' => $urls,
        ];
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
                collect($validatedData['templates'])->map(function ($template) use ($model) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;

                    $positions = collect($template)
                        ->except('template_id')
                        ->filter(fn($value) => !is_null($value))
                        ->toArray();

                    $model->templates()->attach([
                        $templateId => ['positions' => $positions],
                    ]);
                });

                $model->load('templates');
                $this->handleFiles($model);

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

                        $previewWidth = 300;
                        $previewHeight = 300;

                        $scaleX = $baseWidth / $previewWidth;
                        $scaleY = $baseHeight / $previewHeight;


                        $printX = (int)round($pivotPositions[$sideName . '_x'] * $scaleX);
                        $printY = (int)round($pivotPositions[$sideName . '_y'] * $scaleY);
                        $printW = (int)round($pivotPositions[$sideName . '_width'] * $scaleX);
                        $printH = (int)round($pivotPositions[$sideName . '_height'] * $scaleY);
                        $angle = (float)($pivotPositions[$sideName . '_angle'] ?? 0);


                        if ($printW <= 0) $printW = (int)round($baseWidth * 0.3);
                        if ($printH <= 0) $printH = (int)round($baseHeight * 0.3);

                        $firstMockup = $this->repository
                            ->query()
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
                            'hex' => $firstMockup->colors ? $firstMockup->colors[0] : null,
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
            }

            return $model;
        });

        return $model;
    }
    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        dd($validatedData);

        $model = $this->handleTransaction(function () use ($id, $validatedData, $relationsToLoad) {

            $model = $this->repository->update($id, $validatedData);

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

            } else {
                $model->templates()->detach();
            }



           if (request()->allFiles())
           {
               $this->handleFiles($model,true);
               $model->clearMediaCollection('generated_mockups');

           }

            foreach ($model->templates as $template) {
                $pivotPositions = $template->pivot->positions ?? [];

                collect($model->types)->each(function ($type) use ($model, $template, $pivotPositions) {
                    $sideName = strtolower($type->value->name);

                    $baseMedia = $model->getMedia('mockups')
                        ->first(fn($m) =>
                            $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'base'
                        );

                    $maskMedia = $model->getMedia('mockups')
                        ->first(fn($m) =>
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
                        return;
                    }

                    $basePath = $baseMedia->getPath();
                    if (!is_file($basePath)) {
                        return;
                    }

                    [$baseWidth, $baseHeight] = getimagesize($basePath);

                $previewWidth  = 800;
                    $previewHeight = 800;

                    $scaleX = $baseWidth / $previewWidth;
                    $scaleY = $baseHeight / $previewHeight;

                    $xKey     = "{$sideName}_x";
                    $yKey     = "{$sideName}_y";
                    $wKey     = "{$sideName}_width";
                    $hKey     = "{$sideName}_height";
                    $angleKey = "{$sideName}_angle";

                    $canvasX = (float)($pivotPositions[$xKey] ?? 0);
                    $canvasY = (float)($pivotPositions[$yKey] ?? 0);
                    $canvasW = (float)($pivotPositions[$wKey] ?? 0);
                    $canvasH = (float)($pivotPositions[$hKey] ?? 0);
                    $angle   = (float)($pivotPositions[$angleKey] ?? 0);


                    $printX = (int)round($canvasX * $scaleX);
                    $printY = (int)round($canvasY * $scaleY);
                    $printW = (int)round($canvasW * $scaleX);
                    $printH = (int)round($canvasH * $scaleY);


                    if ($printW <= 0) $printW = (int)round($baseWidth * 0.3);
                    if ($printH <= 0) $printH = (int)round($baseHeight * 0.3);


                    $firstMockup = $this->repository
                        ->query()
                        ->whereBelongsTo($model->category)
                        ->first();

                    $hexColor = $firstMockup && $firstMockup->colors
                        ? $firstMockup->colors[0]
                        : null;


                    $binary = (new MockupRenderer())->render([
                        'base_path'   => $basePath,
                        'shirt_path'  => $maskMedia->getPath(),
                        'design_path' => $designMedia->getPath(),
                        'print_x'     => $printX,
                        'print_y'     => $printY,
                        'print_w'     => $printW,
                        'print_h'     => $printH,
                        'angle'       => $angle,
                        'hex'         => $hexColor,
                    ]);


                    $model
                        ->addMediaFromString($binary)
                        ->usingFileName("mockup_{$sideName}_{$template->id}.png")
                        ->withCustomProperties([
                            'side'        => $sideName,
                            'template_id' => $template->id,
                        ])
                        ->toMediaCollection('generated_mockups');
                });
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
