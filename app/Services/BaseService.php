<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Http\UploadedFile;

class BaseService
{
    protected array $relations = [];


    public function __construct(public BaseRepositoryInterface $repository){}


    public function getAll(bool $paginate = false, $columns = ['*'])
    {
        return $this->repository->all($paginate, $columns, $this->relations);
    }

    public function showResource($id)
    {
        $model = $this->repository->find($id);
        return $model;

    }

    public function storeResource($validatedData, $relationsToStore = [])
    {
        dd($validatedData);
        $model = $this->repository->create($validatedData);
        $model->load($this->relations);

        collect($relationsToStore)->map(function ($relation) use ($validatedData, $model) {
            $model->{$relation}()->createMany($validatedData[$relation]);
        });
        if (collect($validatedData)->contains(function ($value) {
            dd($value);
            return $value instanceof UploadedFile;
        })) {
            handleMediaUploads($validatedData, $model);
        }
        return $model;
    }

    public function updateResource($request, $id)
    {
        $model = $this->repository->update($request->validated(), $id);
        $model->load($this->relations);
        $files = $request->allFiles();
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


}
