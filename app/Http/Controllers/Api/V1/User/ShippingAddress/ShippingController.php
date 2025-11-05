<?php

namespace App\Http\Controllers\Api\V1\User\ShippingAddress;

use App\DTOs\Shipping\RateQuoteDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use App\Repositories\Interfaces\ShipmentRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Services\Shipping\ShippingManger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;


class ShippingController extends Controller
{
    public function __construct(public ShippingManger                     $shippingManger,
                                public CartRepositoryInterface            $cartRepository,
                                public ShippingAddressRepositoryInterface $shippingAddressRepository,
                                public PaymentMethodRepositoryInterface   $paymentMethodRepository,
                                public ShipmentRepositoryInterface        $shipmentRepository,
    )
    {
    }

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
            'shipping_address_id' => ['nullable', 'integer', 'exists:shipping_addresses,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);
        $cart = $this->cartRepository->find($validatedData['cart_id']);
        if (Arr::get($validatedData, 'shipping_address_id')) {
            $shippingAddress = $this->shippingAddressRepository->find($validatedData['shipping_address_id']);
            $paymentMethod = $this->paymentMethodRepository->find($validatedData['payment_method_id']);
            $rateQuoteDto = RateQuoteDTO::fromArray($cart,
                $paymentMethod->code == 'cash_on_delivery',
                $shippingAddress->zone?->state?->country?->id);
//            $result = $this->shippingManger->driver('shipblu')->getRateQuote($rateQuoteDto, 'delivery');
//            $cart->update([
//                'delivery_amount' => $result['total']
//            ]);
        } else {
            if ($cart->delivery_amount !== 0) {
                $cart->update([
                    'delivery_amount' => 0
                ]);
            }
        }

        return Response::api(data: CartResource::make($cart));

    }

    public function requestPickup(Request $request)
    {
        $validatedData = $request->validate([
            'shipment_ids' => ['required', 'array',],
            'shipment_ids.*' => ['required', 'integer', 'exists:shipments,id']
        ]);
        $trackingNumbers = $this->shipmentRepository->query()
            ->whereIn('id', $validatedData['shipment_ids'])
            ->pluck('tracking_number')
            ->toArray();

//        $result = $this->shippingManger->driver('shipblu')->requestPickup($trackingNumbers);

//        $this->shipmentRepository->query()
//            ->whereIn('id', $validatedData['shipment_ids'])->update([
//                'status' => $result[0]["status"]
//            ]);
        return Response::api();

    }

}
