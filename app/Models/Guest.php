<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    protected $fillable = [
        'cookie_value',
    ];

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }
}
