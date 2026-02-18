<?php

namespace App\Enums;

use App\Helpers\EnumHelpers;

enum CornerEnum : int
{
    use EnumHelpers;
  case SHARP = 0;
  case Rounded = 1;

    public function label()
    {
        return match ($this) {
            self::SHARP => "Sharp",
            self::Rounded => "Rounded",
        };
    }
}
