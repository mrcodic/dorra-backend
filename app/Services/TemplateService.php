<?php

namespace App\Services;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
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
        $storedImagePath = null;
dd($validatedData->preview_image)   ;
        if ($validatedData->preview_image instanceof UploadedFile) {
            $file = $validatedData->preview_image;
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $storedImagePath = $file->storeAs('templates', $fileName);
        }

        $model = $this->repository->create([
            'name' => $validatedData->name,
            'status' => $validatedData->status,
            'product_id' => $validatedData->product_id,
            'design_data' => $validatedData->design_data,
            'preview_image' => $storedImagePath,
            'source_design_svg' => $validatedData->source_design_svg,
        ]);

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
