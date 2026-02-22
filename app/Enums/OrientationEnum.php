<?php

namespace App\Enums;

use App\Helpers\EnumHelpers;

enum OrientationEnum : int
{
    use EnumHelpers;
    case HORIZONTAL = 2;
    case VERTICAL = 1;


    public function label()
    {
        return match ($this) {
            self::VERTICAL => "Vertical",
            self::HORIZONTAL => "Horizontal",
        };
    }

}
