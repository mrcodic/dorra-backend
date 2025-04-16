<?php

namespace App\Enums\Product;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case DRAFTED = 1;
    case PUBLISHED = 2;

}
