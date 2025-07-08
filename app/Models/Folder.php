<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphMany};


class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function designs(): MorphMany
    {
        return $this->morphMany(Design::class, 'designable');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
