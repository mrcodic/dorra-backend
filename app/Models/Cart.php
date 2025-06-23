<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cart extends Model
{
    protected $fillable = [
        "user_id",
        "cookie_id",
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
    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot([
            'design_id', 'sub_total','total_price'
        ]);
    }
}
