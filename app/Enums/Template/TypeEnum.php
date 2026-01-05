<?php

namespace App\Enums\Template;

use App\Helpers\EnumHelpers;

enum TypeEnum : int
{
    use EnumHelpers;

    case FRONT = 1;
    case BACK = 2;
    case NONE = 3;


    public function key(): string
    {
        return match ($this) {
            self::FRONT => 'front',
            self::BACK  => 'back',
            self::NONE  => 'none',
        };
    }

    public static function keys(): array
    {
        return array_map(fn(self $e) => $e->key(), self::cases());
    }
    public function label()
    {
        return match ($this) {
            self::FRONT => "Front",
            self::BACK => "Back",
            self::NONE => "None",

        };
    }
}
