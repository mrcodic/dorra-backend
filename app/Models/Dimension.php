<?php

namespace App\Models;

use App\Enums\Product\UnitEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dimension extends Model
{
    protected $fillable = [
        'name', 'width', 'height', 'unit', 'is_custom',
    ];
    protected $casts = [
        'unit' => UnitEnum::class,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function name(): Attribute
    {
        return Attribute::set(function ($value, $attributes) {
            return $attributes['width'] . '*' . $attributes['height'];
        });
    }

}
