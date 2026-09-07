<?php

namespace App\Enums\Bundle;

enum RepeatTypeEnum: string
{
    case ONCE = 'once';
    case REPEAT = 'repeat';

    public function label(): string
    {
        return match ($this) {
            self::ONCE => 'Apply Once',
            self::REPEAT => 'Repeat Based On Quantity',
        };
    }
}
