<?php

namespace App\Enums\Permission;

use App\Helpers\EnumHelpers;

enum PermissionAction: string
{
    use EnumHelpers;
    case Index = 'Index';
    case Create = 'Create';
    case Show = 'Show';
    case Update = 'Update';
    case Delete = 'Delete';

}
