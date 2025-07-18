<?php

namespace App\Models;

use App\Models\Mockup;
use App\Enums\Product\StatusEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasManyThrough, MorphMany, MorphToMany};

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description',];
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'sub_category_id',
        'has_custom_prices',
        'is_free_shipping',
        'base_price',
        'status',
        'has_mockup',
    ];

    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;
        });
    }

    public function rating(): Attribute
    {
        return Attribute::get(fn(?int $value) => $this->load('reviews:rating')->reviews?->pluck('rating')->avg());
    }

    public function scopeWithReviewRating(Builder $builder, $rates): Builder
    {
        $rates = is_array($rates) ? $rates : explode(',', $rates);
        return $builder->whereHas('reviews', function ($query) use ($rates) {
            $query->whereIn('rating', $rates);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault(['name' => 'uncategorized']);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->withPivot([
                'taggable_id',
                'taggable_type',
            ])
            ->withTimestamps();
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class)->withTimestamps();
    }

    public function specificationOptions(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductSpecificationOption::class,
            ProductSpecification::class,
            'product_id',
            'product_specification_id',
            'id',
            'id'
        );
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, 'savable');
    }

    public function mockups(): HasMany
    {
        return $this->hasMany(Mockup::class);
    }

    public function saves(): MorphMany
    {
        return $this->morphMany(Save::class, 'savable');
    }

    public function getAllProductImages()
    {
        return $this->getMedia('product_extra_images')
            ->merge($this->getMedia('product_main_image'));
    }



    public function getMainImageUrl(): string
    {
        return $this->getFirstMediaUrl('product_main_image');
    }

    public function getExtraImagesUrl(): string
    {
        return $this->getFirstMediaUrl('product_extra_images');
    }

    protected function casts()
    {
        return [
            'status' => StatusEnum::class,
        ];
    }


}
