<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable =[
        'name',
        'price',
        'credits',
        'description',
        'is_active',
    ];
    public function scopeActive($query)
    {
       return $query->where('is_active',1);
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
}
