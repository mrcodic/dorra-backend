<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ProductSpecification extends Model
{
    protected $fillable = [
        'name',
        'product_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductSpecificationOption::class);
    }
}
