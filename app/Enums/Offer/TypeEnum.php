<?php

namespace App\Enums\Offer;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;
    case CATEGORY = 1;
    case PRODUCT = 2;


    public function label()
    {
        return match ($this) {
            self::CATEGORY => __('Category'),
            self::PRODUCT => __('Product'),

        };
    }

}
