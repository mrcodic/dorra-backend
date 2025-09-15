<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description'];
    protected $fillable = ['name', 'description', 'parent_id', 'is_landing', 'has_mockup', 'base_price', 'has_custom_prices', 'is_has_category','show_add_cart_btn','show_customize_design_btn'];
    protected $attributes = ['is_has_category' => 1];

    public function scopeIsLanding(Builder $builder): Builder
    {
        return $builder->where('is_landing', true);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function designs()
    {
        return $this->morphToMany(Design::class, 'designable', 'designables')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function templates()
    {
        return $this->morphToMany(
            Template::class,
            'referenceable',
            'product_template',
            'referenceable_id',
            'template_id'
        )->withTimestamps();
    }

    public function subCategoryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');

    }

    public function categoryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')->whereNull('sub_category_id');
    }

    public function landingProducts(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'categorable')->withTimestamps();
    }

    public function landingSubCategories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'categorable')->withTimestamps();
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(ProductPrice::class, 'pricable');
    }
    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function dimensions()
    {
        return $this->morphToMany(Dimension::class, 'dimensionable', 'dimension_product')->withTimestamps();
    }

    public function specifications(): MorphMany
    {
        return $this->morphMany(ProductSpecification::class, 'specifiable');
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
    public function carts(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'cartable');
    }
}
