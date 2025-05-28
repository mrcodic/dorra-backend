<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\TemplateResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Template\{StoreTemplateRequest, UpdateTemplateRequest};


class TemplateController extends DashboardController
{
    public function __construct(public TemplateService $templateService, public ProductRepositoryInterface $productRepository)
    {
        parent::__construct($templateService);
        $this->storeRequestClass = new StoreTemplateRequest();
        $this->updateRequestClass = new UpdateTemplateRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
        $this->assoiciatedData = [
            'index' => [
                'products' => $this->productRepository->all(),
            ]
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->templateService->getData();
    }

    public function getProductTemplates()
    {
        $productId = request()->input('productId');
        $templates = $this->templateService->getProductTemplates($productId);
        return Response::api(data: TemplateResource::collection($templates));

    }

}
