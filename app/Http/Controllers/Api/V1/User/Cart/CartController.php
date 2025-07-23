<?php

namespace App\Http\Controllers\Api\V1\User\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\AddToCartRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\CartItem;
use App\Models\Design;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CartController extends Controller
{
    public function __construct(public CartService $cartService){}

    public function store(AddToCartRequest $request)
    {
        $this->cartService->storeResource($request);
        return Response::api(message: "Item added to cart successfully");
    }

    public function index()
    {
        $cart = $this->cartService->getCurrentUserOrGuestCart();
        $data = $cart ? CartResource::make($cart) : (object)[];
        return Response::api(data: $data);
    }

    public function destroy(Request $request)
    {
        $request->validate(['design_id' => 'required', 'exists:designs,id']);
        $this->cartService->deleteItemFromCart($request->design_id);
        return Response::api();
    }

    public function applyDiscount(Request $request)
    {
        $data = $this->cartService->applyDiscount($request);
        return Response::api(data: $data);
    }

    public function cartInfo()
    {
        $data = $this->cartService->cartInfo();
        return Response::api(data: $data);
    }

    public function addQuantity(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => ['required_without:product_price_id', 'integer', 'min:1'],
            'product_price_id' => ['required_without:quantity', 'integer', 'exists:product_prices,id',
                function ($attribute, $value, $fail) use ($itemId) {
                    $cartItem = CartItem::find($itemId);
                    if (!$cartItem || !$cartItem->product->prices->pluck('id')->contains($value)) {
                        $fail('The selected product price is not valid for the current Item.');
                    }
                }
            ],
        ]);
        $this->cartService->addQuantity($request, $itemId);
        return Response::api();
    }

}
