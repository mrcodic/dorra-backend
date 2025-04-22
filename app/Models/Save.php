<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class Save extends Model
{
    protected $fillable = [
        'savable_id',
        'savable_type',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savable(): MorphTo
    {
        return $this->morphTo();
    }
}
