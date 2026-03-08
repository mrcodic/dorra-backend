<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    public function templates(): MorphToMany
    {
        return $this->morphedByMany(
            Template::class,
            'mediable'
        );
    }
    public function designs(): MorphToMany
    {
        return $this->morphedByMany(
            Design::class,
            'mediable'
        );
    }
}
