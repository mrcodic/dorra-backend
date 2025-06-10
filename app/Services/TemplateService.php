<?php

namespace App\Services;

use App\Jobs\ProcessBase64Image;
use App\Jobs\RenderFabricJsonToPngJob;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class TemplateService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(TemplateRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getAll(
        $relations = [],
        bool $paginate = false,
        $columns = ['*'],
        $perPage = 16
    ) {

        $requested = request('per_page', $perPage);
        $pageSize  = $requested === 'all' ? null : (int) $requested;


        $query = $this->repository
            ->query(['id', 'name', 'product_id', 'status', 'created_at'])
            ->with(['product:id,name', 'product.tags'])
            ->when(request()->filled('search_value'), function ($q) {
                $locale = app()->getLocale();
                $q->where("name->{$locale}", 'LIKE', '%' . request('search_value') . '%');
            })
            ->when(request()->filled('product_id'), fn ($q) => $q->whereProductId(request('product_id')))
            ->when(request()->filled('status'),     fn ($q) => $q->whereStatus(request('status')))
            ->latest();


        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()                                   // “all”
                : $query->paginate($pageSize)->withQueryString(); // 16 / 50 / 100
        }


        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters : $this->filters,
            perPage : $pageSize ?? $perPage
        );
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {
            $model = $this->repository->create($validatedData);
            if (isset($validatedData['specifications']))
            {
                $model->specifications()->attach($validatedData['specifications']);
            }
            if (isset($validatedData['base64_preview_image']))
            {
                ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
            }

            return $model->refresh();
        });
        /*if (isset($validatedData['design_data']))
        {
            RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $model, 'templates');
        }*/
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $id) {
            $model = $this->repository->update($validatedData,$id);
            if (isset($validatedData['specifications']))
            {
                $model->specifications()->sync($validatedData['specifications']);
            }
            return $model;
        });
        if (isset($validatedData['base64_preview_image']))
        {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }
        /*if (isset($validatedData['design_data']))
      {
          RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $model, 'templates');
      }*/
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }

    public function getProductTemplates($productId)
    {
        $search = trim(request()->input('search'));
        $type = request()->input('type');
        $tags = array_filter((array) request()->input('tags'));
        $recent = request()->boolean('recent');

        return $this->repository->query()
            ->with('media')
            ->when($search, function ($query) use ($search) {
                $locale = app()->getLocale();
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })
            ->when($type !== null && $type !== '', function ($query) use ($type) {
                $query->whereType($type);
            })
            ->when(!empty($tags), function ($query) use ($tags) {
                $query->whereHas('product.tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            })
            ->when($recent == true || request()->isNotFilled("recent"), function ($query) use ($recent) {
                $query->whereNotNull('updated_at')
                    ->orderByDesc('updated_at');
            }, function ($query) {
                $query->oldest();
            })
            ->whereProductId($productId)
            ->paginate(10);
    }

//    public function templateCustomizations($validatedData)
//    {
//
//        Cache::put("order_step_data_{$validatedData["user_id"]}",)
//    }



}
