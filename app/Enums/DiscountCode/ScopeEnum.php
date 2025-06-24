<?php

namespace App\Enums\DiscountCode;

use App\Helpers\EnumHelpers;

enum ScopeEnum : int
{
    use EnumHelpers;
    case CATEGORY = 1;
    case PRODUCT = 2;
    case GENERAL = 3;


    public function label()
    {
        return match ($this) {
            self::CATEGORY => __('Category'),
            self::PRODUCT => __('Product'),
            self::GENERAL => __('General'),
        };
    }

}
