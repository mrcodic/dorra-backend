<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphToMany};
use Illuminate\Database\Eloquent\SoftDeletes;


class Folder extends Model
{
    use softDeletes;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function designs(): MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable', 'designables')->withTimestamps();
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
