<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Http\UploadedFile;

class BaseService
{
    protected array $relations = [];

    public function __construct(public BaseRepositoryInterface $repository)
    {
    }


    public function getAll(bool $paginate = false, $columns = ['*'])
    {
        return $this->repository->all($paginate, $columns, $this->relations);
    }

    public function showResource($id)
    {
        $model = $this->repository->find($id);
        return $model;

    }

    public function storeResource($validatedData)
    {
        $model = $this->repository->create($validatedData);
        $model->load($this->relations);
        if (collect($validatedData)->contains(function ($value) {
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
