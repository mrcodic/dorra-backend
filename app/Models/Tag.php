<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $fillable = ['name'];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class,'taggable');
    }
}
