<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FontStyle extends Model implements HasMedia
{
    use  InteractsWithMedia;
    protected $fillable = [
        'name',
        'font_id',
    ];
    public function font(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Font::class);
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }
}
