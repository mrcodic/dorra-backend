<?php

namespace App\Enums\Offer;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;
    case CATEGORY = 2;
    case PRODUCT = 1;


    public function label()
    {
        return match ($this) {
            self::CATEGORY => __('Category'),
            self::PRODUCT => __('Product'),

        };
    }

}
