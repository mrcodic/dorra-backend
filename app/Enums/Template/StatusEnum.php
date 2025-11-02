<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case DRAFTED = 1;
    case PUBLISHED = 2;
    case LIVE = 3;

    public function label()
    {
        return match ($this) {
            self::DRAFTED => "Draft",
            self::PUBLISHED => "Published",
            self::LIVE => "Live",
        };
    }

    /** Background color (HEX) */
    public function bgHex(): string
    {
        return match ($this) {
            self::DRAFTED   => '#F3F4F6', // gray-100
            self::PUBLISHED => '#DCFCE7', // green-100
            self::LIVE      => '#FEF3C7', // amber-100
        };
    }

    /** Optional text color (HEX) */
    public function textHex(): string
    {
        return match ($this) {
            self::DRAFTED   => '#374151', // gray-700
            self::PUBLISHED => '#065F46', // green-800
            self::LIVE      => '#92400E', // amber-800
        };
    }

}
