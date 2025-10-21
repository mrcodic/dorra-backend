<?php

namespace App\Services;

use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleService extends BaseService
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $query = $this->repository->query();
        if (request()->ajax())
        {
            $locale = app()->getLocale();
           $query =  $query->when(request()->filled('search'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            });
        }
        return $query->with($relations)->get();

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
