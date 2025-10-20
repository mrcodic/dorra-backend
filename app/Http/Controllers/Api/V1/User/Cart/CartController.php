<?php

namespace App\Http\Controllers\Api\V1\User\Cart;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\{StoreCartItemRequest, UpdateCartItemRequest};
use App\Http\Resources\Cart\{CartItemResource, CartResource};
use App\Models\{CartItem};
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\{Rule, ValidationException};

class CartController extends Controller
{
    public function __construct(public CartService $cartService)
    {
    }

    public function store(StoreCartItemRequest $request)
    {
        $cart = $this->cartService->storeResource($request);
        return Response::api(message: "Item added to cart successfully", data: [
            'cookie_value' => $cart->guest?->cookie_value,
        ]);
    }

    public function index()
    {
        $cart = $this->cartService->getCurrentUserOrGuestCart();
        if ($cart === false)
        {
            return Response::api(statusCode: HttpEnum::GONE,errors:[
                'message' => ['Cart expired']
            ]);
        }
        $data = $cart ? CartResource::make($cart) : (object)[];
        return Response::api(data: $data);
    }

    /**
     * @throws ValidationException
     */
    public function destroy(Request $request)
    {
        $request->validate(['item_id' => 'required', 'exists:items,id']);
        $message = $this->cartService->deleteItemFromCart($request->item_id);
        return Response::api(message: $message);
    }

    public function applyDiscount(Request $request)
    {

        return Response::api(data: $this->cartService->applyDiscount($request));
    }

    public function removeDiscount(Request $request)
    {
        $this->cartService->removeDiscount();
        return Response::api(data: (object)[]);
    }

    public function cartInfo()
    {
        $data = $this->cartService->cartInfo();
        return Response::api(data: $data);
    }

    public function addQuantity(Request $request, $itemId)
    {
        $cartItem = CartItem::find($itemId);

        $request->validate([
            'quantity' => ['required_without:product_price_id', 'integer', 'min:1'],
            'product_price_id' => [
                Rule::requiredIf(function () use ($cartItem) {
                    return $cartItem && $cartItem->cartable->has_custom_prices;
                }),
                'integer',
                'exists:product_prices,id',
                function ($attribute, $value, $fail) use ($cartItem) {
                    if ($cartItem && !$cartItem->cartable->prices->pluck('id')->contains($value)) {
                        $fail('The selected product price is not valid for the current item.');
                    }
                },
            ],

        ]);
        $this->cartService->addQuantity($request, $itemId);
        return Response::api();
    }

    public function priceDetails($itemId)
    {
        $itemSpecs = $this->cartService->priceDetails($itemId);
        return Response::api(data: new CartItemResource($itemSpecs));
    }

    public function updatePriceDetails(UpdateCartItemRequest $request, $itemId)
    {
        $result = $this->cartService->updatePriceDetails($request->validated(), $itemId);
        return Response::api(message: $result[0],data: new CartItemResource($result[1]));
    }

    public function checkItem(Request $request)
    {
        $result = $this->cartService->checkItem($request);
        return Response::api(data: ['is_add_to_cart' => $result]);

    }

}
