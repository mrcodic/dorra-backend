<?php

namespace App\DTOs\Order;

use App\Enums\Order\OrderTypeEnum;
use App\Models\Location;
use App\Models\ShippingAddress;

class OrderAddressData
{
    public static function fromRequest($request): array
    {
        $type = OrderTypeEnum::from($request->type);

        $location = $type === OrderTypeEnum::PICKUP
            ? Location::find($request->location_id)
            : null;

        $shipping = $type === OrderTypeEnum::SHIPPING
            ? ShippingAddress::find($request->shipping_address_id)
            : null;

        return [
            'type' => $type->value,
            'location_id' => $location?->id,
            'location_name' => $location?->name,
            'location_link' => $location?->link,
            'shipping_address_id' => $shipping?->id,


            'address_label' => $type === OrderTypeEnum::SHIPPING
                ? $shipping?->label
                : ($location?->label ?? 'Pickup Location'),

            'address_line' => $type === OrderTypeEnum::SHIPPING
                ? $shipping?->line
                : $location?->address_line,

            'state' => $type === OrderTypeEnum::SHIPPING
                ? $shipping?->state->name
                : $location?->state->name,

            'country' => $type === OrderTypeEnum::SHIPPING
                ? $shipping?->state->country->name
                : $location?->state->country->name,

            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->full_phone_number,
        ];
    }
}
