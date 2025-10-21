<?php

namespace App\Services;

use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleService extends BaseService
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        $model->load($relationsToLoad);
        if (isset($validatedData['permissions'])) {
            $model->syncPermissions($validatedData['permissions'] );
        }
        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData,$id);
        $model->load($relationsToLoad);
        if (isset($validatedData['permissions'])) {
            $model->syncPermissions($validatedData['permissions'] );
        }
        return $model->load($relationsToLoad);
    }
}
