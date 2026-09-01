<?php

namespace App\Models;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AiGuideQuestion extends Model
{
    use HasTranslations;
    protected $translatable = ['title','placeholder','prompt_label'];
    protected $fillable = [
        'key',
        'title',
        'type',
        'prompt_label',
        'placeholder',
        'required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'type' => AiGuideQuestionTypeEnum::class,
        'required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AiGuideQuestionOption::class)->orderBy('sort_order');
    }
}
