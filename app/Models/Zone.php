<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Zone extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'code', 'state_id'];
    public $translatable = ['name'];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function mappings(): MorphMany
    {
        return $this->morphMany(ShippingLocationMapping::class, 'locatable');
    }
}
