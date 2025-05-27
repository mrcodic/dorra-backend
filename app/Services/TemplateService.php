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

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create([
            'name' => $validatedData->name,
            'status' => $validatedData->status,
            'product_id' => $validatedData->product_id,
            'design_data' => $validatedData->design_data,
        ]);
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
