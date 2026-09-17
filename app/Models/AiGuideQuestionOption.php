<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class AiGuideQuestionOption extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;
    protected $translatable = ['label','prompt_value'];

    protected $fillable = [
        'ai_guide_question_id',
        'value',
        'label',
        'prompt_value',
        'sort_order',
        'ui_data'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'ui_data' => 'array',
    ];

    public function aiGuideQuestion(): BelongsTo
    {
        return $this->belongsTo(AiGuideQuestion::class);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(getMediaCollectionName('option_image'))->singleFile();
    }
}
