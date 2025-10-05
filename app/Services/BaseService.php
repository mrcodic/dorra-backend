<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Traits\HandlesTryCatch;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BaseService
{
    use HandlesTryCatch;

    protected array $filters = [];

    public function __construct(public BaseRepositoryInterface $repository)
    {
    }


    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10,$counts =[])
    {
        return $this->repository->all($paginate, $columns, $relations, filters: $this->filters, perPage: $perPage,counts: $counts);
    }

    public function showResource($id, $relations = [])
    {
        $model = $this->repository->find($id, $relations);
        return $model;

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        collect($relationsToStore)->map(function ($relation) use ($validatedData, $model) {
            if (isset($validatedData[$relation])) {
                $model->{$relation}()->createMany($validatedData[$relation]);
            }
            $model->{$relation}()->create($validatedData);;
        });
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        $files = request()->allFiles();
        if ($files) {
            handleMediaUploads($files, $model, clearExisting: true);
        }
        if (request()->has('deleted_old_images')) {
            collect(request()->deleted_old_images)->each(function ($id) {
                Media::find($id)?->delete();
            });
        }

        return $model->load($relationsToLoad);
    }

    public function deleteResource($id)
    {
        $model = $this->repository->find($id);
        if (method_exists($model, 'hasMedia') && $model->hasMedia()) {
            clearMediaCollections($model);
        }
        return $model->delete();
    }

    public function bulkDeleteResources($ids)
    {
        return $this->handleTransaction(function () use ($ids) {
            $models = $this->repository->query()->whereIn('id', $ids)->get();

            $models->each(function ($model) {
                if (method_exists($model, 'hasMedia') && $model->hasMedia()) {
                    if ($model->hasMedia()) {
                        clearMediaCollections($model);
                    }
                }
            });
            return $this->repository->query()->whereIn('id', $ids)->delete();
        });


    }

    public function bulkRestore($ids)
    {
        return $this->repository->query()->onlyTrashed()->whereIn('id', $ids)->restore();

    }


}
