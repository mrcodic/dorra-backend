<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
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

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        if (isset($validatedData['preview_image'])) {
            $path = $validatedData['preview_image']->store('public/templates');

            $validatedData['preview_image'] = str_replace('public/', '', $path);
        }
        $model = $this->repository->create($validatedData);
        return $model->load($relationsToLoad);

    }

    public function getProductTemplates($productId)
    {
        return $this->repository->query()->whereProductId($productId)->get();
    }
    public function getData(): JsonResponse
    {
        $templates = $this->repository
            ->query(['id', 'name', 'preview_png', 'product_id', 'updated_at'])
            ->with(['product:id,name'])
            ->latest();

        return DataTables::of($templates)
            ->addColumn('name', function ($template) {
                return $template->getTranslation('name', app()->getLocale());
            })
            ->addColumn('status', function ($template) {
                return $template->status?->label() ?? '';
            })
            ->make(true);
    }


}
