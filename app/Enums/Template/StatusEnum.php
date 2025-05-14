<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case DRAFTED = 1;
    case APPROVED = 2;
    case LIVE = 3;

}
