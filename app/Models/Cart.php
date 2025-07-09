<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cart extends Model
{
    protected $fillable = [
        "user_id",
        "guest_id",
        "price",
    ];

    public function price(): Attribute
    {
        return Attribute::get(function ($value){
           return fmod($value, 1) == 0.0 ? (int)$value : $value;
        });
    }

    public function designs()
    {
        return $this->belongsToMany(Design::class,'cart_items')
            ->withTimestamps();
    }
    public function cartItems()
    {
        return $this->belongsToMany(Design::class, 'cart_items')
            ->using(CartItem::class)
            ->withPivot(['sub_total', 'total_price'])
            ->withTimestamps();
    }
}
