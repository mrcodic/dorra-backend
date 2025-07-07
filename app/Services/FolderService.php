<?php

namespace App\Services;

use App\Repositories\Interfaces\{FolderRepositoryInterface};
use Illuminate\Http\Request;


class FolderService extends BaseService
{
    public function __construct(FolderRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getUserFolders()
    {
        return $this->repository->query()
            ->withCount('designs')
            ->when(request()->filled('search'),
                fn($query) => $query->where('name', 'like', '%' . request()->search . '%'))
            ->whereBelongsTo(auth('sanctum')->user())
            ->get();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        if (!empty($validatedData['designs'])) {
            $model->designs()->sync($validatedData['designs']);
        }

        return $model->load($relationsToLoad);
    }

    public function assignDesignsToFolder($validatedData)
    {
       return $this->repository->query()->find($validatedData['folder_id'])->designs()->sync($validatedData['designs']);
    }

    public function bulkDeleteResources($ids)
    {
        return $this->repository->query()->whereIn('id', $ids)->delete();

    }


}
