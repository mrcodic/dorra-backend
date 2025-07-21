<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;

    case FRONT = 1;
    case BACK = 2;
    case FRONT_AND_BACK = 3;
    case NONE = 4;



    public function label()
    {
        return match ($this) {
            self::FRONT => "Front",
            self::BACK => "Back",
            self::FRONT_AND_BACK => "Front and Back",

            self::NONE => "None",

        };
    }
}
