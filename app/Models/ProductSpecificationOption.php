<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductSpecificationOption extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'product_specification_id',
        'value',
        'price',
    ];
}
