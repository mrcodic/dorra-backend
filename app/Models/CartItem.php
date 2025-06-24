<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CartItem extends Pivot
{
    protected $table = 'cart_items';
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }
}
