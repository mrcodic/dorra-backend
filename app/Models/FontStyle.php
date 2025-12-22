<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FontStyle extends Model implements HasMedia
{
    use  InteractsWithMedia;
    protected $fillable = [
        'name',
        'font_id',
    ];
}
