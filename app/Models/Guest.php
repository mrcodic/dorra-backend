<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Guest extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'cookie_value',
    ];

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }


    public function cartItems(): HasManyThrough
    {
        return $this->hasManyThrough(CartItem::class, Cart::class);
    }
    public function addresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }
    public function designs(): MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable', 'designables')->withTimestamps();
    }
}
