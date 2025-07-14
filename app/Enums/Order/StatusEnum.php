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

    case PAID = 7;
    case FAILED = 8;
    public function label()
    {
        return match ($this) {
            self::PENDING => "Pending",
            self::CONFIRMED => "Confirmed",
            self::PREPARED => "Prepared",
            self::SHIPPED => "Shipped",
            self::DELIVERED => "Delivered",
        };
    }

    public static function toArray(): array
    {
        return collect(self::cases())->map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
            ];
        })->toArray();
    }


}
