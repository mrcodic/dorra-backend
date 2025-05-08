<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\CategoryService;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Category\{StoreCategoryRequest, UpdateCategoryRequest};


class TemplateController extends DashboardController
{
    public function __construct(public TemplateService $templateService)
    {
        parent::__construct($templateService);
        $this->storeRequestClass = new StoreCategoryRequest();
        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }


}
