<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Models\Plan;
use App\Models\Transaction;
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


class CreditOrderController extends Controller
{

    public function index()
    {
        return view('dashboard.credit-orders.index');
    }
    public function getData()
    {
        return 1;
    }



}
