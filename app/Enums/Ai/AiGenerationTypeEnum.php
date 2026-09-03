<?php

namespace App\Enums\Ai;

enum AiGenerationTypeEnum: string
{
    case IMAGE = 'image';
    case PATTERN = 'pattern';
    case LOGO = 'logo';
    case BACKGROUND = 'background';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::PATTERN => 'Pattern',
            self::LOGO => 'Logo',
            self::BACKGROUND => 'Background',
        };
    }
}
