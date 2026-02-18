<?php

namespace App\Enums\Product;

use App\Helpers\EnumHelpers;

enum CuttingEnum: int
{
    use EnumHelpers;

    case SHARP   = 1;
    case ROUNDED = 2;



    public function label(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $labels = $this->labelLocales();
        return $labels[$locale] ?? $labels['en'] ?? $this->name;
    }


    public function labelLocales(): array
    {
        return match ($this) {
            self::SHARP => [
                'en' => 'Sharp',
                'ar' => 'حاد',
            ],
            self::ROUNDED => [
                'en' => 'Rounded',
                'ar' => 'دائري',
            ],
        };
    }


    public function imagePath(): string
    {
        return match ($this) {
            self::SHARP   => 'images/cutting/sharp.jpg',
            self::ROUNDED => 'images/cutting/rounded.jpg',
        };
    }


    public function imageUrl(): string
    {
        return asset($this->imagePath());
    }


    public static function toSelectOptions(): array
    {
        return array_map(fn(self $e) => [
            'value' => $e->value,
            'key'   => $e->name,
            'label' => $e->label(),
            'image' => $e->imageUrl(),
        ], self::cases());
    }
}
