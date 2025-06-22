<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cart extends Model
{
    protected $fillable = [
        "user_id",
        "cookie_id",
        "price",
    ];

    public function designs()
    {
        return $this->belongsToMany(Design::class,'cart_items')
            ->withTimestamps();
    }
    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items');
    }
}
