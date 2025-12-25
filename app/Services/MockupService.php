<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Jobs\HandleMockupFilesJob;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\BaseService;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Arr;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository, public MockupRenderer $renderer
        , public ProductRepositoryInterface               $productRepository
    )
    {
        parent::__construct($repository);
    }

    public function getMockups(): array
    {
        $categoryId = request('product_id');
        $productType = request('type');
        $templateId = request('template_id');
        $color = request('color');


        $categoryId = $productType === 'category'
            ? $categoryId
            : $this->productRepository
                ->query()
                ->whereId($categoryId)
                ->value('category_id');


        $mockups = $this->repository
            ->query()
            ->when($categoryId, fn($q) => $q->whereCategoryId($categoryId))
            ->when($templateId, fn($q) => $q->whereHas('templates', fn($qq) => $qq->where('templates.id', $templateId)
            )
            )
            ->with([
                'templates:id',
                'types:id,value',
                'media' => fn($q) => $q->whereIn('collection_name', [
                    'mockups',
                    'generated_mockups',
                    'templates',
                    'back_templates',
                ]),
            ])
            ->get();

        $colors = $mockups
            ->flatMap(fn($mockup) => $mockup->templates
                ->filter(fn($tpl) => $tpl->id == $templateId)
                ->flatMap(function ($tpl) {
                    $c = $tpl->pivot->colors ?? [];
                    if (is_string($c)) {
                        $c = json_decode($c, true) ?: [];
                    }
                    return is_array($c) ? $c : [];
                })
            )
            ->filter()
            ->unique()
            ->values()
            ->all();


        $requestedColor = $color
            ? (str_starts_with($color, '#') ? strtolower($color) : '#' . strtolower($color))
            : null;

        $activeColor = $requestedColor ?? (count($colors) ? strtolower($colors[0]) : null);

        $media = $mockups
            ->filter(fn($mockup) => $mockup->templates->contains('id', $templateId))
            ->flatMap(function ($mockup) use ($templateId, $activeColor) {

                $mockupMedia = $mockup->media
                    ->where('collection_name', 'generated_mockups')
                    ->filter(function ($m) use ($templateId, $activeColor) {

                        if ($m->getCustomProperty('template_id') != $templateId) {
                            return false;
                        }

                        $hex = strtolower($m->getCustomProperty('hex', ''));

                        return !$activeColor || $hex === strtolower($activeColor);
                    });

                $sides = $mockupMedia->pluck('custom_properties.side')->unique();
                if ($sides->contains('front') && $sides->contains('back')) {
                    return $mockupMedia
                        ->groupBy(fn($m) => $m->model_id . '_' . $m->getCustomProperty('side'))
                        ->flatten();
                }

                return $mockupMedia
                    ->groupBy(fn($m) => $m->model_id . '_' . $m->getCustomProperty('side'))
                    ->map(fn($group) => $group->first());
            })
            ->values();

        $pickBySide = function ($media, string $side): array {
            return $media
                ->filter(fn($m) => $m->getCustomProperty('side') === $side)
                ->map(fn($m) => $m->getFullUrl())
                ->values()
                ->all();
        };

        $front = $pickBySide($media, 'front');
        $back = $pickBySide($media, 'back');
        $none = $pickBySide($media, 'none');

        $urls = array_values(array_merge($front, $back, $none));

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
            ->when(
                request()->filled('template_id') &&
                request()->filled('category_id') &&
                request()->filled('color')
                && request()->filled('mockup_id'),
                fn($q) => $q
                    ->whereKeyNot(request('mockup_id'))
                    ->where('category_id', request('category_id'))
                    ->whereHas('templates', function ($query) {
                        $query
                            ->where('templates.id', request('template_id'))
                            ->whereJsonContains('mockup_template.colors', request('color'));
                    })
            )
            ->when(request()->filled('product_ids'), fn($q) => $q->whereIn('category_id', request()->array('product_ids')))
            ->when(request()->filled('type'), fn($q) => $q->whereHas('types', fn($q) => $q->where('types.id', request('type'))))
            ->when(request()->filled('types'), function ($query) {
                $types = array_map('intval', request()->input('types'));
                $query->whereHas('types', function ($q) use ($types) {
                    $q->whereIn('types.value', $types);
                }, '=', count($types));

                $query->whereDoesntHave('types', function ($q) use ($types) {
                    $q->whereNotIn('types.value', $types);
                });
            })
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
            collect($validatedData['templates'])->each(function ($template) use ($model) {
                $templateId = $template['template_id'] ?? null;
                if (!$templateId) return;

                $positions = collect($template)
                    ->except(['template_id', 'colors'])
                    ->filter(fn($value) => !is_null($value))
                    ->toArray();

                $colors = Arr::get($template, 'colors', []);

                $colors = collect($colors)
                    ->filter(fn($c) => is_string($c) && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c))
                    ->values()
                    ->all();


                $model->templates()->syncWithoutDetaching([
                    $templateId => [
                        'positions' => $positions,
                        'colors' => $colors,
                    ],
                ]);
            });

            return $model;
        });
        $this->handleFiles($model);
        $model->load(['templates', 'types', 'category', 'media']);
        HandleMockupFilesJob::dispatch($model)->delay(now()->addSeconds(5));
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

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($id, $validatedData) {

            $model = $this->repository->update($validatedData, $id);

            // Sync types (OK)

            $selectedTypeValues = Arr::get($validatedData, 'types', []);
            $modelTypesValues = $model->types->pluck('value.value')->toArray();


            $typesChanged = collect($selectedTypeValues)->sort()->values()->all()
                != collect($modelTypesValues)->sort()->values()->all();

            $model->types()->sync($selectedTypeValues);
            // Sync templates + pivot data
            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {
                $syncData = [];

                $templatesInput->each(function ($template) use (&$syncData) {
                    $templateId = $template['template_id'] ?? null;
                    if (!$templateId) return;

                    // Positions: everything except template_id & colors
                    $positions = collect($template)
                        ->except(['template_id', 'colors'])
                        ->filter(fn($v) => $v !== null && $v !== '')
                        ->toArray();

                    // Colors: normalize + validate
                    $colors = Arr::get($template, 'colors', []);
                    if (is_string($colors)) {
                        $colors = json_decode($colors, true) ?: [];
                    }
                    if (!is_array($colors)) {
                        $colors = [];
                    }

                    $colors = collect($colors)
                        ->filter(fn($c) => is_string($c)
                            && preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $c))
                        ->values()
                        ->all();

                    $syncData[$templateId] = [
                        'positions' => $positions,
                        'colors' => $colors,
                    ];
                });
                if ($typesChanged) {
                    $model->templates()->sync($syncData);
                }else{
                    $model->templates()->syncWithoutDetaching($syncData);

                }
            }

            if (request()->allFiles()) {
                $this->handleFiles($model, true);
            }

            $model->load(['templates', 'types', 'category', 'media']);
            HandleMockupFilesJob::dispatch($model)->delay(now()->addSeconds(5));

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
