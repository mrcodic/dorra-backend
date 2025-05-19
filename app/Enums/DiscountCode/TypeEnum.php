<?php

namespace App\Enums\DiscountCode;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;
    case FIXED = 1;
    case PERCENTAGE = 2;

    public function label()
    {
        return match ($this) {
            self::FIXED => __('Fixed'),
            self::PERCENTAGE => __('Percentage'),
        };
    }

}
