<?php

namespace App\Enums\Invoice;

use App\Helpers\EnumHelpers;

enum InvoiceStatusEnum: int
{
    use EnumHelpers;

    case PAID = 1;
    case PENDING = 2;
    case FAILED = 3;


    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::PENDING => 'Pending',
            self::FAILED => 'Failed',
        };
    }
}
