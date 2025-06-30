<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(Design::class)->withTimestamps();
    }
}
