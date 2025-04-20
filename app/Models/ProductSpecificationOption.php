<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ProductSpecificationOption extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;
    protected $fillable = [
        'product_specification_id',
        'value',
        'price',
    ];

    protected $translatable = ['value'];
}
