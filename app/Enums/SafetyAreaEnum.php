<?php

namespace App\Enums;

use App\Helpers\EnumHelpers;

enum SafetyAreaEnum : int
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
         return $this->value .'Cm';
     }

}
