<?php

namespace App\DTOs\Shipping;

use App\Models\Order;
use App\Models\ShippingLocationMapping;
use App\Models\Zone;

class AddressDTO
{
    public function __construct(
        public Order $order,
    )
    {
    }

    public static function fromArray($order): AddressDTO
    {
        return new self(
            order: $order,
        );
    }

    public function toShipBluPayload(): array
    {
        $order = $this->order;
        return [
            'customer' => [
                'full_name' => $order->orderAddress->first_name . ' ' . $order->orderAddress->last_name,
                'email' => $order->orderAddress->email,
                'phone' => $order->orderAddress->phone,
                'address' => [
                    'line_1' => $order->orderAddress->shippingAddress->line,
                    'line_2' => $order->orderAddress->shippingAddress->line,
                    'zone' =>  (int) $this->providerZoneId($order->orderAddress->shippingAddress->zone_id, 'shipblu'),
                ],
                'packages' => [
                    'package_size' => $order->items->count(),
                ]
            ]
        ];
    }

    function providerZoneId(int $zoneId, string $provider): ?string
    {
        return ShippingLocationMapping::where([
            'provider' => $provider,
            'locatable_type' => Zone::class,
            'locatable_id' => $zoneId,
        ])->value('external_id');
    }
}
