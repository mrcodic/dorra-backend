<?php

namespace App\Enums\Item;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;

    case DOWNLOAD = 1;
    case PRINT = 2;


    public function label()
    {
        return match ($this) {
            self::DOWNLOAD => "Download",
            self::PRINT => "Print",
        };
    }
}
