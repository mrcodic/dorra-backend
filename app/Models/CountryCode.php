<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CountryCode extends Model
{
    protected $fillable = [
        'country_name',
        'iso_code',
        'phone_code',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
