<?php

namespace App\Enums;

use App\Helpers\EnumHelpers;

enum BorderEnum : int
{
    use EnumHelpers;
    case R10 = 10;
    case R15 = 15;
    case R20 = 20;
    case R25 = 25;
    case R30 = 30;
    case R35 = 35;
    case R40 = 40;

    public function label(): string
    {
        $dpi = config('printing.dpi', 96);
        $cm  = self::pxToCm($this->value, $dpi);

        return sprintf('%s cm', $cm);
    }

    public static function pxToCm(int $px, int $dpi = 96, int $precision = 2): string
    {
        $cm = $px * 2.54 / max(1, $dpi);
        return number_format($cm, $precision, '.', '');
    }


    public static function cmToPx(float $cm, int $dpi = 96): int
    {
        return (int) round($cm * $dpi / 2.54);
    }

}
