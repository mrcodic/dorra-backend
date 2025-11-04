<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShippingLocationMapping extends Model
{
    protected $fillable = [
        'provider',
        'external_id',
        'locatable_id',
        'locatable_type',
    ];

    public function locatable(): MorphTo
    {
        return $this->morphTo();
    }
}
