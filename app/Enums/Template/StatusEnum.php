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

}
