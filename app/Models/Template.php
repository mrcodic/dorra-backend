<?php

namespace App\Models;

use App\Enums\OrientationEnum;
use App\Enums\Product\UnitEnum;
use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Observers\TemplateObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(TemplateObserver::class)]
class Template extends Model implements HasMedia
{
    use HasUuids, HasTranslations, InteractsWithMedia,SoftDeletes;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'status',
        'design_data',
        'design_back_data',
        'description',
        'price',
        'is_landing',
        'colors',
        'orientation',
        'dimension_id',
        'has_corner',
        'has_safety_area',
        'safety_area',
        'cut_margin',
        'border',
        'approach',
        'supported_languages',
        'is_best_seller',
        'use_front_as_back',

    ];
    protected $casts = [
        'supported_languages' => 'array',
        'status' => StatusEnum::class,
        'type' => TypeEnum::class,
        'unit' => UnitEnum::class,
        'colors' => 'array',
        'orientation' => OrientationEnum::class,
    ];

    protected $attributes = [
        'status' => StatusEnum::DRAFTED,
    ];
    public function tableauScenes(): BelongsToMany
    {
        return $this->belongsToMany(TableauScene::class,)
            ->using(TableauSceneTemplate::class)
            ->withPivot('positions')
            ->withTimestamps();
    }
    public function scopeIsLanding(Builder $builder): Builder
    {
        return $builder->where('is_landing', true);
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->whereStatus($status);
    }

    public function getImageAttribute(): string
    {
        return $this->approach == 'without_editor' ?
            $this->getFirstMediaUrl('templates-preview') ?:
                "" :
            $this->getFirstMediaUrl('templates');

    }
    public function getImageMediaAttribute(): string
    {
        return $this->approach == 'without_editor' ?
            $this->getFirstMedia('templates-preview') ?:
                "" :
            $this->getFirstMedia('templates');

    }
    public function getPreviewImageUrlForType(string $type): string
    {
        $type = strtolower($type);
        $isWithoutEditor = $this->approach === 'without_editor';

        return match ($type) {
            'front' => $isWithoutEditor ? $this->getFirstMediaUrl('templates-preview') ?: "" : ($this->getFirstMediaUrl('templates') ?: ""),

            'back' => $this->use_front_as_back
                ? $this->getImageUrlForType('front')  // reuse front logic
                : ($isWithoutEditor ? $this->getFirstMediaUrl('back-templates-preview') ?: "" : ($this->getFirstMediaUrl('back_templates') ?: "")),

            'none' => $isWithoutEditor ? $this->getFirstMediaUrl('templates-preview') ?: "" : ($this->getFirstMediaUrl('templates') ?: ""),

            default => ""
        };
    }

    public function scopeLive(Builder $builder): Builder
    {
        return $builder->where('status', StatusEnum::LIVE);
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    public function products()
    {
        return $this->morphedByMany(
            Product::class,
            'referenceable',
            'product_template',
            'template_id',
            'referenceable_id'
        )->withTimestamps();
    }

    public function categories()
    {
        return $this->morphedByMany(
            Category::class,
            'referenceable',
            'product_template',
            'template_id',
            'referenceable_id'
        )->withTimestamps();
    }


    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

    public function industries()
    {
        return $this->morphToMany(Industry::class, 'industryable', 'industryables')->withTimestamps();
    }

    public function flags()
    {
        return $this->morphToMany(Flag::class, 'flaggable')->withTimestamps();
    }

    public function mockups()
    {
        return $this->belongsToMany(
            Mockup::class,
            'mockup_template',

        )
            ->using(MockupTemplate::class)
            ->withPivot(['id', 'positions', 'colors','model_color','type'])
            ->withTimestamps();
    }

    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }

    public function libraryMedia(): MorphToMany
    {
        return $this->morphToMany(
            Media::class,
            'mediable'
        )->withPivot('type')->withTimestamps();
    }

    public function getImageUrlForType(string $type): string
    {
        $type = strtolower($type);
        return match ($type) {
            'front' => $this->getFrontImageUrl(),
            'back' => $this->getBackImageUrl(),

            default => $this->getNoneImageUrl(),
        };
    }

    public function getFrontImageUrl(): string
    {
        return $this->getFirstMediaUrl('templates') ?: asset('images/default-product.png');
    }

    public function getBackImageUrl(): string
    {
        return $this->getFirstMediaUrl('back_templates') ?: asset('images/default-product.png');
    }

    public function getNoneImageUrl(): string
    {
        return $this->getFirstMediaUrl('templates') ?: asset('images/default-product.png');
    }

    public function registerMediaConversions($media = null): void
    {
        $this->addMediaConversion('front_png')
            ->format('png')
            ->performOnCollections('templates')
            ->nonQueued();

        $this->addMediaConversion('front_jpg')
            ->format('jpg')
            ->performOnCollections('templates')
            ->nonQueued();

        $this->addMediaConversion('back_png')
            ->format('png')
            ->performOnCollections('back_templates')
            ->nonQueued();

        $this->addMediaConversion('back_jpg')
            ->format('jpg')
            ->performOnCollections('back_templates')
            ->nonQueued();

        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 518,518)
            ->nonQueued();
    }
}
