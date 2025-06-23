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
    public static function toArray(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }



    public static function tryFromName(string $name): ?self
{
    foreach (self::cases() as $case) {
        if ($case->name === $name) {
            return $case;
        }
    }
    return null;
}




}
