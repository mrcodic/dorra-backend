<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DesignSpecification extends Pivot
{
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductSpecificationOption::class, 'option_id');
    }
}
