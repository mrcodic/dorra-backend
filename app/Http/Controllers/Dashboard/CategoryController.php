<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Category\{StoreCategoryRequest, UpdateCategoryRequest};


class CategoryController extends DashboardController
{
   public function __construct(public CategoryService $categoryService)
   {
       parent::__construct($categoryService);
       $this->storeRequestClass = new StoreCategoryRequest();
       $this->updateRequestClass = new UpdateCategoryRequest();
       $this->indexView = 'categories.index';
       $this->createView = 'categories.create';
       $this->editView = 'categories.edit';
       $this->showView = 'categories.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }
}
