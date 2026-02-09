<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Models\Plan;
use App\Models\Transaction;
use App\Services\CreditOrderService;
use App\Http\Requests\Order\{StoreOrderRequest, UpdateOrderRequest};
use App\Models\Location;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;


class CreditOrderController extends DashboardController
{

    public function __construct(
        public CreditOrderService              $creditOrderService,
    )
    {
        parent::__construct($creditOrderService);
//        $this->storeRequestClass = new StoreCategoryRequest();
//        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'credit-orders.index';
        $this->createView = 'credit-orders.create';
        $this->usePagination = true;

        $this->resourceTable = 'credit_orders';
    }
    public function getData(): JsonResponse
    {
        return $this->creditOrderService->getData();
    }



}
