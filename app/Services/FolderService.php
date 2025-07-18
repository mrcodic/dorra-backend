<?php

namespace App\Services;

use App\Repositories\Interfaces\{DesignRepositoryInterface, FolderRepositoryInterface};
use Illuminate\Http\Request;


class FolderService extends BaseService
{
    public function __construct(FolderRepositoryInterface $repository,public DesignRepositoryInterface $designRepository)
    {
        parent::__construct($repository);
    }

    public function getUserFolders()
    {
        return $this->repository->query()
            ->withCount('designs')
            ->when(request()->filled('search'), function ($query) {
                $search = request('search');
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->where('user_id', auth('sanctum')->id())
            ->orderBy('created_at',request('date','desc'))
            ->get();
    }
    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);

        if (!empty($validatedData['designs'])) {
            $model->designs()->attach($validatedData['designs']);
        }

        return $model->load($relationsToLoad);
    }


    public function assignDesignsToFolder($validatedData)
    {
       return $this->repository->query()->find($validatedData['folder_id'])->designs()->syncWithoutDetaching($validatedData['designs']);
    }

    public function bulkDeleteResources($ids)
    {
        $folders= $this->repository->query()->whereIn('id', $ids)->get();

        collect($folders)->each(function ($folder) {
            collect($folder->designs)->each(function ($design) {
                $design->delete();
            });
            $folder->designs()->detach();
            $folder->delete();

        });
    }
    public function bulkForceDeleteResources($ids)
    {
        $folders= $this->repository->query()->withTrashed()->whereIn('id', $ids)->get();

        collect($folders)->each(function ($folder) {
            collect($folder->designs)->each(function ($design) {
                $design->forceDelete();
            });
            $folder->designs()->detach();
            $folder->forceDelete();
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad =[])
    {
        $model = $this->repository->update($validatedData, $id);
        if (!empty($validatedData['designs'])) {
            $model->designs()->syncWithoutDetaching($validatedData['designs']);
        }
        return $model->load($relationsToLoad);
    }
    public function trash()
    {
        return $this->repository->query()
            ->onlyTrashed()
            ->withCount('designs')
            ->where('user_id', auth('sanctum')->id())
            ->get();
    }

}
