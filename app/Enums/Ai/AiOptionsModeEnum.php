<?php

namespace App\Enums\Ai;

use App\Helpers\EnumHelpers;

enum AiOptionsModeEnum: string
{
    use EnumHelpers;
    case INHERIT = 'inherit';
    case MERGE = 'merge';
    case REPLACE = 'replace';
}
