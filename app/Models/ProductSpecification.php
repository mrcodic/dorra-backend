<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\Translatable\HasTranslations;


class ProductSpecification extends Model
{
    use HasTranslations;

    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'specifiable_id',
        'specifiable_type',
        'product_id',
        'type',
        'fixed_key',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function specifiable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductSpecificationOption::class);
    }


}
