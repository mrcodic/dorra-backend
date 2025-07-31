<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Category\{StoreCategoryRequest, UpdateCategoryRequest};


class CategoryController extends DashboardController
{
    public function __construct(public CategoryService $categoryService)
    {
        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreCategoryRequest();
        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'categories.index';
        $this->usePagination = true;
        $this->resourceClass = CategoryResource::class;
        $this->resourceTable = 'categories';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }
    public function search(Request $request)
    {
        $categories = $this->categoryService->search($request);
        return $this->resourceClass::collection($categories);
    }

}
