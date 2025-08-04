<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description'];
    protected $fillable = ['name', 'description', 'parent_id', 'is_landing'];

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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
        return $this->morphedByMany(Product::class, 'categorable');
    }
    public function landingSubCategories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'categorable');
    }
}
