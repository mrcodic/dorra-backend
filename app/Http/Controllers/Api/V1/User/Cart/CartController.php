<?php

namespace App\Http\Controllers\Api\V1\User\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\Design\DesignResource;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CartController extends Controller
{
    public function __construct(public CartService $cartService){}

    public function store(AddToCartRequest $request)
    {
        $this->cartService->storeResource($request->only(['price','design_id','user_id','cookie_id']));
        return Response::api(message: "Item added to cart successfully");
    }

    public function index()
    {
        $cart =  $this->cartService->getCurrentUserOrGuestCart();
        $data =  $cart ? CartResource::make($cart) : (object)[];
        return Response::api(data: $data );
    }

    public function destroy(Request $request)
    {
        $request->validate(['design_id' => 'required','exists:designs,id']);
        $this->cartService->deleteItemFromCart($request->design_id);
        return Response::api();
    }
    public function applyDiscount(Request $request,$cartId)
    {

       $data =  $this->cartService->applyDiscount($request, $cartId);
        return Response::api(data: $data);
    }
}
