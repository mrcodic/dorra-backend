<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Http\Controllers\Controller;
use App\Http\Requests\User\Checkout\CheckoutRequest;
use App\Http\Resources\LocationResource;
use App\Services\LocationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class OrderController extends Controller
{
    public function __construct(public OrderService $orderService, public LocationService $locationService)
    {
    }

    public function checkout(CheckoutRequest $request)
    {
        $this->orderService->checkout($request);
    }

    public function searchLocations(Request $request)
    {
        $locations = $this->locationService->search($request);
        return Response::api(data: LocationResource::collection($locations->load('state.country')));
    }
}
