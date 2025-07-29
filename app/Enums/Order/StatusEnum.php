<?php

namespace App\Enums\Order;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;

    case PENDING = 1;
    case CONFIRMED = 2;
    case PREPARED = 3;
    case SHIPPED = 4;
    case DELIVERED = 5;
    case REFUNDED = 6;
    public function label()
    {
        return match ($this) {
            self::PENDING => "Pending",
            self::CONFIRMED => "Confirmed",
            self::PREPARED => "Prepared",
            self::SHIPPED => "Shipped",
            self::DELIVERED => "Delivered",
            self::REFUNDED => "Refunded",
        };
    }
    public function icon()
    {
        return match ($this) {
            self::PENDING => asset("images/orders/pending.svg"),
            self::CONFIRMED => asset("images/orders/confirmed.svg"),
            self::PREPARED => asset("images/orders/preparing.svg"),
            self::SHIPPED => asset("images/orders/out-for-delivery.svg"),
            self::DELIVERED => asset("images/orders/delivered.svg"),
            self::REFUNDED =>asset("images/orders/refund.svg"),

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
