<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Category\{StoreOrderRequest, UpdateOrderRequest};


class CategoryController extends DashboardController
{
    public function __construct(public CategoryService $categoryService)
    {
        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreOrderRequest();
        $this->updateRequestClass = new UpdateOrderRequest();
        $this->indexView = 'categories.index';
        $this->usePagination = true;
        $this->resourceTable = 'categories';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }


}
