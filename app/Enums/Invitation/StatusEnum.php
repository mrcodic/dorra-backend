<?php

namespace App\Enums\Invitation;

use App\Helpers\EnumHelpers;

enum StatusEnum : int
{
    use EnumHelpers;
    case PENDING = 1;
    case ACCEPTED = 2;

    public function label()
    {
        return match ($this) {
            self::PENDING => "pending",
            self::ACCEPTED => "accepted",
        };
    }

}
