<?php

namespace App\Enums\Payment;

use App\Helpers\EnumHelpers;

enum StatusEnum : string
{
    use EnumHelpers;


    case PENDING = 'pending';
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    public function label()
    {
        return match ($this) {
            self::PENDING => "Pending",
            self::PAID => "Paid",
            self::UNPAID => "Unpaid",
        };
    }




}
