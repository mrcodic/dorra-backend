<?php

namespace App\Enums\Payment;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;


    case PENDING = 1;
    case PAID = 2;
    case UNPAID = 3;
    public function label()
    {
        return match ($this) {
            self::PENDING => "Pending",
            self::PAID => "Paid",
            self::UNPAID => "Unpaid",
        };
    }




}
