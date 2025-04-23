<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\Translatable\HasTranslations;


class ProductSpecification extends Model
{
    use HasTranslations;
    protected $fillable = [
        'name',
        'product_id',
    ];

    public $translatable = ['name'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductSpecificationOption::class);
    }
}
