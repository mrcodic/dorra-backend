<?php

namespace App\Http\Controllers\Api\V1\User\Order;


use App\Enums\HttpEnum;
use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Checkout\CheckoutRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\Order\OrderResource;
use App\Models\OrderItem;
use App\Services\LocationService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        if (Arr::get($order,'paymentDetails') === false)
        {
            return Response::api(HttpEnum::BAD_REQUEST,
                message: __('orders.payment_failed_message'),
                errors: [
                    'error' => [__('orders.payment_failed_error')],
                ]
            );
        }
        if (!$order) {
            return Response::api(statusCode: HttpEnum::BAD_REQUEST, message: 'Bad request', errors: ['message' => ['Cart is empty.']]);
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

    public function cancelOrder($id)
    {
     $this->orderService->cancelOrder($id);
    }

    public function downloadItem(OrderItem $orderItem)
    {
        $itemable = $orderItem->itemable;
        $sides = $itemable->types->pluck('value.key')->toArray();
        dd($sides,$itemable->types->pluck('value')->flatMap(function ($type){
            return [
                $type->key()
            ];
        }));
//        $targetSide = $side === 'none' ? 'front' : $side;
//
//        $conversion = "{$targetSide}_{$format}"; // ex: front_png, back_jpeg
//
//        $media = $targetSide === 'front' ? $mediaFront : $mediaBack;
//        $path  = $media->getPath($conversion); // generates conversion if needed
//
//        $filename = "template-{$template->id}-{$targetSide}.{$extension}";
//
//        return response()->download($path, $filename, [
//            'Content-Type' => "image/{$extension}",
//        ]);


    }
}
