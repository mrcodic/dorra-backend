<?php

namespace App\Enums;

use App\Helpers\EnumHelpers;

enum CornerEnum: int
{
    use EnumHelpers;

    case Rounded = 1;
    case SHARP = 0;

    public function label()
    {
        return match ($this) {
            self::SHARP => "Sharp",
            self::Rounded => "Rounded",
        };
    }
}
