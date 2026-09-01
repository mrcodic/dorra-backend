<?php

namespace App\Enums\Ai;

use App\Helpers\EnumHelpers;

enum AiGuideQuestionTypeEnum: string
{
    use EnumHelpers;
    case SINGLE_SELECT = 'single_select';
    case TEXT = 'text';
    case TEXTAREA = 'textarea';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE_SELECT => 'Single Select',
            self::TEXT => 'Text',
            self::TEXTAREA => 'Textarea',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
