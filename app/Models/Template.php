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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(TemplateObserver::class)]
class Template extends Model implements HasMedia
{
    use HasUuids, HasTranslations, InteractsWithMedia;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'status',
        'design_data',
        'design_back_data',
        'description',
        'is_landing',
        'colors',
        'orientation'
    ];
    protected $casts = [
        'status' => StatusEnum::class,
        'type' => TypeEnum::class,
        'unit' => UnitEnum::class,
        'colors' => 'array',
        'orientation' => OrientationEnum::class,
    ];

    protected $attributes = [
        'status' => StatusEnum::DRAFTED,
    ];
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
        return $this->getFirstMediaUrl('templates') ?: "";

    }
    public function scopeLive(Builder $builder): Builder
    {
        return $builder->where('status', StatusEnum::LIVE);
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
    public function flags()
    {
        return $this->morphToMany(Flag::class, 'flaggable')->withTimestamps();
    }

    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }

    public function getImageUrl(): string
    {
        return $this->getFirstMediaUrl('templates') ?:  asset('images/default-product.png');
    }



}
