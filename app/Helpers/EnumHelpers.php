<?php

namespace App\Helpers;

trait EnumHelpers
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getValuesAsString(): string
    {
        return implode(',',array_column(self::cases(), 'value'));

    }
}
