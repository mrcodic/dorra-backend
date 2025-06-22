<?php

namespace App\Enums\Location;

use App\Helpers\EnumHelpers;

enum DayEnum : int
{
    use EnumHelpers;
   case SATURDAY    = 1;
    case SUNDAY      = 2;
    case MONDAY      = 3;
    case TUESDAY     = 4;
    case WEDNESDAY   = 5;
    case THURSDAY    = 6;
    case FRIDAY      = 7;

    public function label()
    {
        return match ($this) {
            self::SATURDAY   => __('Saturday'),
            self::SUNDAY     => __('Sunday'),
            self::MONDAY     => __('Monday'),
            self::TUESDAY    => __('Tuesday'),
            self::WEDNESDAY  => __('Wednesday'),
            self::THURSDAY   => __('Thursday'),
            self::FRIDAY     => __('Friday'),
        };
    }

}
