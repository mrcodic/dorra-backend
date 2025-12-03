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

        $urls  = [];
        $color = request('color');

        foreach ($mockups as $mockup) {
            foreach ($mockup->templates as $template) {
                foreach ($mockup->types as $type) {
                    $sideName = strtolower($type->value->name);

                    $baseMedia = $mockup->getMedia('mockups')
                        ->first(fn($m) =>
                            $m->getCustomProperty('side') === $sideName &&
                            $m->getCustomProperty('role') === 'base'
                        );

                    $maskMedia = $mockup->getMedia('mockups')
                        ->first(fn($m) =>
                            $m->getCustomProperty('side') === $sideName &&
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
                        'base_path'   => $baseMedia->getPath(),
                        'shirt_path'  => $maskMedia->getPath(),
                        'design_path' => $designMedia->getPath(),
                        'hex'         => $color,
                    ]);

                    $media = $mockup
                        ->addMediaFromString($binary)
                        ->usingFileName("mockup_{$sideName}_{$template->id}_{$color}.png")
                        ->withCustomProperties([
                            'side'        => $sideName,
                            'template_id' => $template->id,
                            'color'       => $color,
                        ])
                        ->toMediaCollection('generated_mockups');

                    $urls[] = $media->getFullUrl();
                }
            }
        }


        $urls = array_values(array_unique($urls));

        return [
            'colors' => $colors,
            'urls'   => $urls,
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
                collect($validatedData['templates'])->map(function ($template) use ($model, $validatedData){
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;
                    $positions = collect($template)
                        ->except('template_id')
                        ->filter(fn($value) => !is_null($value))
                        ->toArray();
                    $model->templates()->attach([
                        $templateId => ['positions' => json_encode($positions)]
                    ]);
                });
                $model->load('templates');
                $this->handleFiles($model);

                foreach ($model->templates as $template) {
                    $pivotPositions = json_decode($template->pivot->positions, true); // Get positions as array

                    collect($model->types)->each(function ($type) use ($model, $template, $pivotPositions) {
                        $sideName = strtolower($type->value->name);

                        $baseMedia = $model->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                                $m->getCustomProperty('role') === 'base');

                        $maskMedia = $model->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                                $m->getCustomProperty('role') === 'mask');

                        if (!$baseMedia || !$maskMedia) {
                            return [$sideName => null];
                        }

                        $designMedia = $type == TypeEnum::BACK
                            ? $template->getFirstMedia('back_templates')
                            : $template->getFirstMedia('templates');

                        if (!$designMedia || !$designMedia->getPath()) {
                            throw new \Exception("Missing design media for {$sideName}");
                        }

                        // Pull positions for this side (front/back/none)
                        $printX = $pivotPositions[$sideName . '_x'] ?? 0;
                        $printY = $pivotPositions[$sideName . '_y'] ?? 0;
                        $printW = $pivotPositions[$sideName . '_width'] ?? 100;  // fallback
                        $printH = $pivotPositions[$sideName . '_height'] ?? 100; // fallback
dd($printX,$printY,$printW,$printH,$sideName . '_x',$pivotPositions);
                        $firstMockup = $this->repository->query()->whereBelongsTo($model->category)->first();

                        $binary = (new MockupRenderer())->render([
                            'base_path' => $baseMedia->getPath(),
                            'shirt_path' => $maskMedia->getPath(),
                            'design_path' => $designMedia->getPath(),
                            'print_x' => $printX,
                            'print_y' => $printY,
                            'print_w' => $printW,
                            'print_h' => $printH,
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


    /**
     * @param mixed $model
     * @return mixed
     */
    public function handleFiles(mixed $model, $clearExisting = false): mixed
    {
        if (!request()->allFiles()) {
            return $model;
        }

        $types      = collect(request()->input('types', []));
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

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {

        $model = $this->handleTransaction(function () use ($validatedData, $id) {

            $model = $this->repository->update($validatedData, $id);


            $model->types()->sync(Arr::get($validatedData, 'types', []));


            $model->clearMediaCollection('generated_mockups');

            $this->handleFiles($model, true);

            DB::table('mockup_position_template')
                ->whereIn('mockup_template_id', $model->templates->pluck('pivot.id'))
                ->delete();

            $model->templates()->detach();


            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {

                $templateIds = $templatesInput->pluck('template_id')->all();
                $model->templates()->sync($templateIds);


                $model->load(['templates', 'types', 'category']);

                $templatesById = $templatesInput->keyBy('template_id');

                $typeMap = [
                    'front' => 1,
                    'back' => 2,
                    'none' => 3,
                ];

                $rows = [];

                foreach ($model->templates as $template) {

                    foreach ($model->types as $type) {

                        $sideName = strtolower($type->value->name);

                        $baseMedia = $model->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                                $m->getCustomProperty('role') === 'base');

                        $maskMedia = $model->getMedia('mockups')
                            ->first(fn($m) => $m->getCustomProperty('side') === $sideName &&
                                $m->getCustomProperty('role') === 'mask');

                        if (!$baseMedia || !$maskMedia) continue;

                        $designMedia = $type == TypeEnum::BACK
                            ? $template->getFirstMedia('back_templates')
                            : $template->getFirstMedia('templates');

                        if (!$designMedia || !$designMedia->getPath()) {
                            throw new \Exception("Missing design media for {$sideName}");
                        }

                        $binary = (new MockupRenderer())->render([
                            'base_path' => $baseMedia->getPath(),
                            'shirt_path' => $maskMedia->getPath(),
                            'design_path' => $designMedia->getPath(),
                            'hex' => $model->colors[0] ?? null,
                        ]);

                        $model
                            ->addMediaFromString($binary)
                            ->usingFileName("mockup_{$sideName}.png")
                            ->withCustomProperties([
                                'side' => $sideName,
                                'template_id' => $template->id,
                            ])
                            ->toMediaCollection('generated_mockups');
                    }


                    $input = $templatesById->get($template->id);
                    if (!$input) continue;

                    $pivotId = $template->pivot->id;

                    foreach ($typeMap as $field => $typeValue) {
                        if (!empty($input[$field])) {
                            $rows[] = [
                                'mockup_template_id' => $pivotId,
                                'position_id' => $input[$field],
                                'template_type' => $typeValue,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
                if (!empty($rows)) {
                    DB::table('mockup_position_template')->insert($rows);
                }
            }

            return $model;
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
