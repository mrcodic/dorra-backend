<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }


    public function getAll(
        $relations = [],
        bool $paginate = false,
        $columns = ['*'],
        $perPage = 16
    )
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $query = $this->repository
            ->query()
            ->with(['product:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                $q->where("name", 'LIKE', '%' . request('search_value') . '%');
            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereProductId(request('product_id')))
            ->when(request()->filled('product_ids'), fn($q) => $q->whereIn('product_id', request()->array('product_ids')))
            ->when(request()->filled('type'), fn($q) => $q->whereType(request('type')))
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
        $model = $this->handleTransaction(function () use ($validatedData, $relationsToLoad) {
            $model = $this->repository->create($validatedData);
            $model->types()->attach(Arr::get($validatedData, 'types') ?? []);
            return $model;
        });
        return $this->handleFiles($model);
    }

    /**
     * @param mixed $model
     * @return mixed
     */
    public function handleFiles(mixed $model, $clearExisting = false): mixed
    {
        if (request()->allFiles()) {
            $types = collect(request()->input('types', []));
            $mediaTypes = collect(['base_image', 'mask_image']);
            $types->each(function ($type) use ($mediaTypes, $model,$clearExisting) {
                $sideName = strtolower(TypeEnum::from($type)->name);
                $mediaTypes->each(function ($mediaType) use ($sideName, $type, $model,$clearExisting) {
                    $inputName = "{$sideName}_{$mediaType}";

                    if (request()->hasFile($inputName)) {
                        $customProperties = [
                            'side' => $sideName,
                            'role' => str_contains($mediaType, 'base') ? 'base' : 'mask',
                        ];

                        handleMediaUploads(
                            request()->file($inputName),
                            $model,
                            customProperties: $customProperties,
                            clearExisting: $clearExisting);
                    }
                });
            });
        }
        return $model;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $id, $relationsToLoad) {
            $model = $this->repository->update($validatedData, $id);
            $model->types()->sync(Arr::get($validatedData, 'types') ?? []);
            return $model;
        });

        return $this->handleFiles($model,true);
    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->repository->find($id);
        return $mockup;
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
