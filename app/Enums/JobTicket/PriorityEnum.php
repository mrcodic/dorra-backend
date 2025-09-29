<?php

namespace App\Enums\JobTicket;

use App\Helpers\EnumHelpers;

enum PriorityEnum : int
{
    use EnumHelpers;

    case STANDARD              = 1;
    case RUSH       = 2;

    public function label(): string
    {
        return match ($this) {
            self::STANDARD        => __('jobticket.priority.standard'),
            self::RUSH       => __('jobticket.priority.rush'),

        };
    }



    public static function toArray(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->toArray();
    }
}
