<?php

namespace App\Enums\Mockup;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;

    case FRONT = 1;
    case BACK = 2;
    case NONE = 3;


    public function label()
    {
        return match ($this) {
            self::FRONT => "Front",
            self::BACK => "Back",
            self::NONE => "None",
        };
    }
}
