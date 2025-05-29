<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum UnitEnum : int
{
    use EnumHelpers;

    case PX = 1;
    case INCH = 2;
    case PERCENTAGE = 3;
    case CM = 4;
    case MM = 5;
    case PT = 6;
    case EM = 7;
    case REM = 8;
    case VW = 9;
    case VH = 10;

    public function label()
    {
        return match ($this) {
            self::PX => "px",
            self::INCH => "inch",
            self::PERCENTAGE => "percentage",
            self::CM => "cm",
            self::MM => "mm",
            self::PT => "pt",
            self::EM => "em",
            self::REM => "rem",
            self::VW => "vw",
            self::VH => "vh",
        };
    }
}
