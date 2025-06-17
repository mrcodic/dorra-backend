<?php

namespace App\Models;

use App\Enums\Product\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough, MorphToMany};
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'sub_category_id',
        'has_custom_prices',
        'is_free_shipping',
        'base_price',
        'status',
    ];

    public $translatable = ['name','description',];

    protected function casts()
    {
        return [
            'status' => StatusEnum::class,
        ];
    }
    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }
    public function rating(): Attribute
    {
        return Attribute::get(fn(?int $value) => $this->reviews?->pluck('rating')->avg());
    }

    public function scopeWithReviewRating(Builder $builder,$rates): Builder
    {
        $rates = is_array($rates) ? $rates : explode(',', $rates);
       return $builder->whereHas('reviews',function ($query) use ($rates){
            $query->whereIn('rating',$rates);
        });
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault(['name' => 'uncategorized']);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class,'sub_category_id');
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
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
    public function specificationOptions(): HasManyThrough
    {
        return $this->hasManyThrough(ProductSpecificationOption::class, ProductSpecification::class,
            'product_id', 'product_specification_id', 'id', 'id');
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
}
