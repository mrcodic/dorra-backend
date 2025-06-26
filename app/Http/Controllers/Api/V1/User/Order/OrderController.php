<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Http\Controllers\Controller;
use App\Http\Requests\User\Checkout\CheckoutRequest;
use App\Services\OrderService;


class OrderController extends Controller
{
    public function __construct(public OrderService $orderService)
    {
    }

    public function checkout(CheckoutRequest $request)
    {
        $this->orderService->checkout($request);
    }
}
