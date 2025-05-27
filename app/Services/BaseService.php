<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BaseService
{
    protected array $filters = [];

    public function __construct(public BaseRepositoryInterface $repository)
    {
    }


    public function getAll($relations = [], bool $paginate = false, $columns = ['*'])
    {
        return $this->repository->all($paginate, $columns, $relations, filters: $this->filters);
    }

    public function showResource($id,$relations=[])
    {
        $model = $this->repository->find($id, $relations);
        return $model->load($relations);

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad =[])
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

    public function updateResource($validatedData, $id, $relationsToLoad =[])
    {
        $model = $this->repository->update($validatedData, $id);
        $model->load($relationsToLoad);
        $files = request()->allFiles();
        if ($files) {
            dd($files);
            handleMediaUploads($files, $model, clearExisting: true);
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
        return $this->repository->query()->whereIn('id', $ids)->delete();

    }


}
