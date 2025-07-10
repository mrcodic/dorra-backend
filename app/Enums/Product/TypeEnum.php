<?php

namespace App\Enums\Product;

use App\Helpers\EnumHelpers;
use App\Models\Product;

enum TypeEnum : string
{
    use EnumHelpers;
    case T_SHIRT = 'T-shirt';
    case OTHER = 'other';


    public function label()
    {
        return match ($this) {
            self::T_SHIRT => __('T-shirt'),
            self::OTHER => __('Other (business cards, flyers, banners, etc.)'),
        };
    }
    public static function availableTypes(): array
    {
        $hasTShirt = Product::where('name->en', self::T_SHIRT->value)->exists();

        if ($hasTShirt) {
            return [
                self::T_SHIRT,
                self::OTHER,
            ];
        }

        return [self::OTHER];
    }

}
