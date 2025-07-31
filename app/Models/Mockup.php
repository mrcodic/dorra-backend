<?php

namespace App\Models;

use App\Enums\Mockup\TypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $keyType = 'int';
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }
}
