<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FavouriteItem extends Model
{
    protected $fillable = [
        'favourite_id',
        'favouritable_type',
        'favouritable_id',
        'contextable_type',
        'contextable_id',
    ];

    public function favourite(): BelongsTo
    {
        return $this->belongsTo(Favourite::class);
    }

    public function favouritable(): MorphTo
    {
        return $this->morphTo();
    }

    public function contextable(): MorphTo
    {
        return $this->morphTo();
    }
}
