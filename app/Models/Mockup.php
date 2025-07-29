<?php

namespace App\Models;

use App\Enums\Mockup\TypeEnum;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'product_id',
        'colors',
        'area_top',
        'area_left',
        'area_height',
        'area_width',
    ];

    protected $casts = [
        'colors' => 'array',
        'type' => TypeEnum::class,
    ];
    protected $attributes = [
        'area_width' => 200,
        'area_left' => 233,
        'area_top' => 233,
        'area_height' => 370,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
