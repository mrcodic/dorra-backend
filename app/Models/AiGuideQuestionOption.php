<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class AiGuideQuestionOption extends Model
{
    use HasTranslations;
    protected $translatable = ['label','prompt_value'];

    protected $fillable = [
        'ai_guide_question_id',
        'value',
        'label',
        'prompt_value',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function aiGuideQuestion(): BelongsTo
    {
        return $this->belongsTo(AiGuideQuestion::class);
    }
}
