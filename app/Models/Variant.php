<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Variant extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'key',
        'variantable_id',
        'variantable_type',
    ];
    public function variantable(): MorphTo
    {
        return $this->morphTo();
    }
}
