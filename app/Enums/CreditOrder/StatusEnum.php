<?php

namespace App\Enums\CreditOrder;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;

    case PENDING = 1;
    case PAID = 2;
    case FAILED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING              => "Pending",
            self::PAID            => "Paid",
            self::FAILED             => "Failed",

        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-light-warning',
            self::PAID    => 'badge-light-success',
            self::FAILED  => 'badge-light-danger',
        };
    }

}
