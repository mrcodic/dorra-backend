<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
#[ObservedBy(CartItemObserver::class)]
class CartItem extends Pivot
{
    protected $table = 'cart_items';

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}
