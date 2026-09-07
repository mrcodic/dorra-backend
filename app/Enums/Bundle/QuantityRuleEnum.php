<?php

namespace App\Enums\Bundle;

enum QuantityRuleEnum: string
{
    case ANY = 'any';
    case MINIMUM = 'minimum';

    public function label(): string
    {
        return match ($this) {
            self::ANY => 'Any Quantity',
            self::MINIMUM => 'Minimum Quantity',
        };
    }
}
