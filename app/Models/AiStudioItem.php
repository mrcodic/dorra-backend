<?php

namespace App\Models;

use App\Enums\Ai\AiGenerationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class AiStudioItem extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'generation_type',
        'credits_cost',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'generation_type' => AiGenerationTypeEnum::class,
        'credits_cost' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (AiStudioItem $studioItem) {
            if (!empty($studioItem->key)) {
                return;
            }

            $name = $studioItem->getTranslation(
                'name',
                'en',
                false
            );

            $baseKey = Str::slug(
                $name ?: 'studio-item',
                '_'
            );

            if (!$baseKey) {
                $baseKey = 'studio_item';
            }

            $key = $baseKey;
            $counter = 2;

            while (
            static::query()
                ->where('key', $key)
                ->exists()
            ) {
                $key = $baseKey . '_' . $counter;
                $counter++;
            }

            $studioItem->key = $key;
        });
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile();
    }
}
