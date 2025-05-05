<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class BaseService
{
    protected array $relations = [];
    protected array $filters = [];


    public function __construct(public BaseRepositoryInterface $repository){}


    public function getAll(bool $paginate = false, $columns = ['*'])
    {
        return $this->repository->all($paginate, $columns, $this->relations , filters: $this->filters);
    }

    public function showResource($id)
    {
        $model = $this->repository->find($id,$this->relations);
        return $model;

    }

    public function storeResource($validatedData, $relationsToStore = [])
    {
        $model = $this->repository->create($validatedData);
        $model->load($this->relations);

        collect($relationsToStore)->map(function ($relation) use ($validatedData, $model) {
                $model->{$relation}()->createMany($validatedData[$relation]);
        });
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model;
    }

    public function updateResource($id, $validatedData)
    {
        $model = $this->repository->update($validatedData, $id);
        $model->load($this->relations);
        $files = request()->allFiles();
        if ($files) {
            handleMediaUploads($files, $model, clearExisting: true);
        }
        return $model;
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
        return $this->repository->query()->whereIn('id',$ids)->delete();

    }


}
