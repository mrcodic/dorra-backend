<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, BelongsTo, MorphToMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{


    protected $fillable = [
        'name',
        'owner_id',
    ];

    public function members(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'teamable');
    }

    public function designs(): MorphToMany
    {
        return $this->morphedByMany(Design::class, 'teamable');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
