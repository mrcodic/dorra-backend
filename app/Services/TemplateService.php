<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TemplateService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(TemplateRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10)
    {

        if (request()->ajax()) {
            $query = $this->repository
                ->query(['id', 'name', 'product_id', 'status', 'created_at'])
                ->with(['product:id,name'])
                ->when(request()->filled('search_value'), function ($query) {
                    $locale = app()->getLocale();
                    $search = request('search_value');
                    $query->where("name->{$locale}", 'LIKE', "%{$search}%");
                })
                ->when(request()->filled('product_id'), function ($query) {
                    $query->whereProductId(request('product_id'));
                })->when(request()->filled('status'), function ($query) {
                    $query->whereStatus(request('status'));
                })
                ->latest();
            if (request('per_page') == "all") {
                return $query->get();
            }
            return $query->paginate(request('per_page',16));
        }
        return $this->repository->all($paginate, $columns, $relations, filters: $this->filters,perPage: 16);

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {
           if (isset($validatedData['unit']) && $validatedData['unit'] == 1)
           {
               $validatedData['width'] = $validatedData['width'] * 25.4 ;
               $validatedData['height'] = $validatedData['height'] * 25.4 ;
               $validatedData['unit'] = 2;
           }
            $model = $this->repository->create($validatedData);
            if (isset($validatedData['specifications']))
            {
                $model->specifications()->attach($validatedData['specifications']);
            }

            return $model;
        });
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }
    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        if (isset($validatedData['unit']) && $validatedData['unit'] == 1)
        {
            $validatedData['width'] = $validatedData['width'] * 25.4 ;
            $validatedData['height'] = $validatedData['height'] * 25.4 ;
            $validatedData['unit'] = 2;
        }
        $model = $this->handleTransaction(function () use ($validatedData, $id) {
            $model = $this->repository->update($validatedData,$id);
            if (isset($validatedData['specifications']))
            {
                $model->specifications()->sync($validatedData['specifications']);
            }
            return $model;
        });
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }


    public function getProductTemplates($productId)
    {
        return $this->repository->query()
            ->with('media')
            ->whereProductId($productId)->latest()->paginate(10);
    }


}
