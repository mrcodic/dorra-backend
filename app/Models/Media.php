<?php

namespace App\Models;


use App\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
//#[ObservedBy(MediaObserver::class)]
class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use SoftDeletes;
    public function templates(): MorphToMany
    {
        return $this->morphedByMany(
            Template::class,
            'mediable'
        );
    }
    public function fonts(): MorphToMany
    {
        return $this->morphedByMany(
            Template::class,
            'mediable'
        )->whereType('font');
    }
    public function designs(): MorphToMany
    {
        return $this->morphedByMany(
            Design::class,
            'mediable'
        );
    }
}
