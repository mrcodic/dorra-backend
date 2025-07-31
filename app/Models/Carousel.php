<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Carousel extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;
    public $translatable = ['title', 'subtitle'];

    protected $fillable =[
        'title',
        'subtitle',
        'product_id'
    ];
}
