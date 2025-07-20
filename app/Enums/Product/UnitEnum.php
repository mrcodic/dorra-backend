<?php

namespace App\Enums\Product;

use App\Helpers\EnumHelpers;

enum UnitEnum : int
{
    use EnumHelpers;


    case PIXEL = 1;
    case CM = 2;


    public function label()
    {
        return match ($this) {
            self::PIXEL => "Pixel",
            self::CM => "Cm",
        };
    }
}
