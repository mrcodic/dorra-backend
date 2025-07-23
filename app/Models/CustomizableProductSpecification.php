<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\{BelongsTo,MorphTo};
use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomizableProductSpecification extends Pivot
{
    protected $table = "customizable";
    protected $fillable = [
        'customizable_id',
        'customizable_type',
        'owner_id',
        'owner_type',
        'product_specification_id',
        'spec_option_id',
    ];

    public function customizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
    public function specOption(): BelongsTo
    {
        return $this->belongsTo(ProductSpecificationOption::class, 'spec_option_id');
    }
    public function productSpecification(): BelongsTo
    {
        return $this->belongsTo(ProductSpecification::class);
    }


}
