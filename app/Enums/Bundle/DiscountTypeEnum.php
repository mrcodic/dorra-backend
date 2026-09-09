<?php

namespace App\Enums\Bundle;

enum DiscountTypeEnum: string
{
    case FREE = 'free';
    case PERCENTAGE = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::PERCENTAGE => 'Percentage Discount',
        };
    }
}
