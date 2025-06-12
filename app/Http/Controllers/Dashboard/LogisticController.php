<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Category\{StoreOrderRequest, UpdateOrderRequest};


class LogisticController extends DashboardController
{
    public function __construct(public CategoryService $categoryService)
    {
        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreOrderRequest();
        $this->updateRequestClass = new UpdateOrderRequest();
        $this->indexView = 'logistics.location';
        $this->usePagination = true;
        $this->resourceTable = 'categories';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }
    public function dashboard()
    {
        return view("dashboard.logistics.dashboard");
    }

}
