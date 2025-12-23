<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Font extends Model
{
    protected $fillable = [
        'name',
    ];

    public function fontStyles(): HasMany
    {
        return $this->hasMany(FontStyle::class);
    }
}
