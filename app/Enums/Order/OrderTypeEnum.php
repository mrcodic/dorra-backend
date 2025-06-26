<?php

namespace App\Enums\Order;

use App\Helpers\EnumHelpers;

enum OrderTypeEnum: int
{
    use EnumHelpers;

    case SHIPPING = 1;
    case PICKUP = 2;

    public function label(): string
    {
        return match ($this) {
            self::SHIPPING => 'Shipping',
            self::PICKUP => 'Pickup',
        };
    }
}
