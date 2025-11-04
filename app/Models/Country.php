<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasTranslations;

    public $translatable = ['name'];
    protected $fillable = ['name', 'code'];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
    public function mappings(): MorphMany
    {
        return $this->morphMany(ShippingLocationMapping::class, 'locatable');
    }
}
