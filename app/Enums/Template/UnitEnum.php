<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum UnitEnum : int
{
    use EnumHelpers;


    case INCH = 1;
    case MM = 2;


    public function label()
    {
        return match ($this) {
            self::INCH => "inch",
            self::MM => "mm",
        };
    }
}
