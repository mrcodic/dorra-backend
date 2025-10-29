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
        $dpi = 300;
        $cm  = self::pxToCm($this->value);

        return $this->value.' Px '. "($cm Cm)";
    }

    public static function pxToCm(int $px, int $precision = 2): string
    {

        $cm = $px * 2.54;
        return number_format($cm, $precision, '.', '');
    }


    public static function cmToPx(float $cm): int
    {
        return (int) round($cm / 2.54);
    }
}
