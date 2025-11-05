<?php

namespace App\Http\Controllers\Api\V1\User\ShippingAddress;

use App\DTOs\Shipping\RateQuoteDTO;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Services\Shipping\ShippingManger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class ShippingController extends Controller
{
    public function __construct(public ShippingManger                     $shippingManger,
                                public CartRepositoryInterface            $cartRepository,
                                public ShippingAddressRepositoryInterface $shippingAddressRepository
    ){}

    public function governorates()
    {
        return Response::api(data: $this->shippingManger->driver('shipblu')->governorates()
        );
    }

    public function cities($governorateId)
    {
        return Response::api(data: $this->shippingManger->driver('shipblu')->cities($governorateId)
        );
    }

    public function zones($cityId)
    {
        return Response::api(data: $this->shippingManger->driver('shipblu')->zones($cityId)
        );
    }

    public function deliveryFee(Request $request)
    {
       $validatedData = $request->validate([
            'cart_id' => ['required', 'integer', 'exists:carts,id'],
            'shipping_address_id' => ['required', 'integer', 'exists:shipping_addresses,id'],
            'cod' => ['required', 'boolean'],
        ]);
        $cart = $this->cartRepository->find($validatedData['cart_id']);
        $shippingAddress = $this->shippingAddressRepository->find($validatedData['shipping_address_id']);
        $rateQuoteDto = RateQuoteDTO::fromArray($cart, $validatedData['cod'], $shippingAddress->zone->state->country->id);
        $result = $this->shippingManger->driver('shipblu')->getRateQuote($rateQuoteDto, 'delivery');
        $cart->update([
            'delivery_amount' => $result['total']
        ]);
        return Response::api();

    }

}
