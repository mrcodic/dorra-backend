<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Enums\HttpEnum;
use App\Enums\Order\StatusEnum;
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
    public function __construct(public OrderService $orderService, public LocationService $locationService){}

    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:' . StatusEnum::getValuesAsString()]
        ]);
        return Response::api(data: OrderResource::collection($this->orderService->userOrders())->response()->getData(true));

    }

    public function show($id)
    {
        return Response::api(data: OrderResource::make($this->orderService->showUserOrder($id)));
    }

    public function checkout(CheckoutRequest $request)
    {
        $order = $this->orderService->checkout($request);
        if ($order['paymentDetails'] === false)
        {
            return Response::api(HttpEnum::BAD_REQUEST,
                message: 'Something went wrong',
                errors: [
                    'error' =>[ 'Failed to payment transaction try again later.'],
                ]
            );
        }
        if (!$order) {
            return Response::api(statusCode: HttpEnum::BAD_REQUEST, message: 'Bad request', errors: ['message' => 'Cart is empty.']);
        }
        return Response::api(data: $order);
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
