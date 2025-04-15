<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphToMany};
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault(['name' => 'uncategorized']);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }



}
