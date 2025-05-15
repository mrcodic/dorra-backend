<?php

namespace App\Enums\Product;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case DRAFTED = 1;
    case PUBLISHED = 2;

    public function label()
    {
        return match ($this) {
            self::DRAFTED => __('Draft'),
            self::PUBLISHED => __('Published'),
        };
    }

}
