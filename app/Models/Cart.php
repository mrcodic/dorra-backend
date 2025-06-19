<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
