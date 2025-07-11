<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Checkout\CheckoutRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\Order\OrderResource;
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
        $order = $this->orderService->checkout($request);
        if (!$order) {
            return Response::api(statusCode: HttpEnum::BAD_REQUEST, message: 'Bad request', errors: ['message' => 'Cart is empty.']);
        }
        return Response::api(data: ['id' => $order->id, 'number' => $order->order_number]);
    }

    public function searchLocations(Request $request)
    {
        $locations = $this->locationService->search($request);
        return Response::api(data: LocationResource::collection($locations->load('state.country')));
    }

    public function trackOrder($id)
    {
        $order = $this->orderService->trackOrder($id);
        return Response::api(data: OrderResource::make($order->load(['orderAddress', 'orderItems'])));

    }

    public function orderStatuses()
    {
        return Response::api(data: $this->orderService->orderStatuses());
    }
}
