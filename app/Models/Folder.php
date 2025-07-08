<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphToMany};


class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function designs(): MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable', 'designables');
    }



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
