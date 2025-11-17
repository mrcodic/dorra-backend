<?php

namespace App\Enums\Order;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;

    case PENDING = 1;
    case CONFIRMED = 2;
    case PREPARED = 3;
    case REQUESTED_PICKUP = 4;
    case SHIPPED = 5;
    case OUT_FOR_DELIVERY = 6;
    case IN_TRANSIT = 7;
    case DELIVERY_ATTEMPTED = 8;
    case DELIVERED = 9;
    case REFUNDED = 10;
    public function label(): string
    {
        return match ($this) {
            self::PENDING              => __('orders.status.pending'),
            self::CONFIRMED            => __('orders.status.confirmed'),
            self::PREPARED             => __('orders.status.prepared'),
            self::REQUESTED_PICKUP     => __('orders.status.requested_pickup'),
            self::SHIPPED              => __('orders.status.shipped'),
            self::OUT_FOR_DELIVERY     => __('orders.status.out_for_delivery'),
            self::IN_TRANSIT           => __('orders.status.in_transit'),
            self::DELIVERY_ATTEMPTED   => __('orders.status.delivery_attempted'),
            self::DELIVERED            => __('orders.status.delivered'),
            self::REFUNDED             => __('orders.status.refunded'),
        };
    }

    public function icon()
    {
        return match ($this) {
            self::PENDING              => asset("images/orders/pending.svg"),
            self::CONFIRMED            => asset("images/orders/confirmed.svg"),
            self::PREPARED             => asset("images/orders/preparing.svg"),
            self::REQUESTED_PICKUP, self::SHIPPED,
            self::OUT_FOR_DELIVERY, self::IN_TRANSIT,
            self::DELIVERED, self::DELIVERY_ATTEMPTED => asset("images/orders/delivered.svg"),
            self::REFUNDED             => asset("images/orders/refund.svg"),
        };
    }


    public static function toArray(): array
    {
        return collect(self::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
                'icon' => asset($case->icon()),
            ];
        })->toArray();
    }


}
