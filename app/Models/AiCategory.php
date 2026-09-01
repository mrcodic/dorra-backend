<?php

namespace App\Models;

use App\Enums\Ai\AiGenerationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphToMany};

class AiCategory extends Model
{
    protected $fillable = [
        'category_id',
        'prompt_template_id',
        'enabled',
        'generation_type',
        'default_resolution',
        'aspect_ratio',
        'credits_cost',
        'provider',
        'model',
        'settings',
        'sort_order',
    ];

    protected $casts = [
        'generation_type' => AiGenerationTypeEnum::class,
        'enabled' => 'boolean',
        'settings' => 'array',
        'credits_cost' => 'integer',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function promptTemplate(): BelongsTo
    {
        return $this->belongsTo(AiPromptTemplate::class, 'prompt_template_id');
    }

    public function questions(): MorphToMany
    {
        return $this->morphToMany(
            AiGuideQuestion::class,
            'assignable',
            'ai_guide_question_assignments'
        )
            ->withPivot([
                'required',
                'is_active',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function options(): MorphToMany
    {
        return $this->morphToMany(
            AiGuideQuestionOption::class,
            'assignable',
            'ai_guide_option_assignments'
        )
            ->withPivot([
                'prompt_value_override',
                'is_active',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
