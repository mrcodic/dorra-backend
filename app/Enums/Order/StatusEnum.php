<?php

namespace App\Enums\Order;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case PLACED = 1;
    case CONFIRMED = 2;
    case PREPARED = 3;
    case SHIPPED = 4;
    case DELIVERED = 5;

    public function label()
    {
        return match ($this) {
            self::PLACED => "Placed",
            self::CONFIRMED => "Confirmed",
            self::PREPARED => "Prepared",
            self::SHIPPED => "Shipped",
            self::DELIVERED => "Delivered",
        };
    }

}
