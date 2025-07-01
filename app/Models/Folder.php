<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};


class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(Design::class)->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
