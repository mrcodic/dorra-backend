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
    public function getWidthPixelAttribute()
    {
        $value = $this->unit === UnitEnum::CM
            ? round($this->width / 2.54, 2)
            : $this->width;
        return fmod($value, 1) == 0.0 ? (int)$value : $value;
    }

    public function getHeightPixelAttribute()
    {
        $value = $this->unit === UnitEnum::CM
            ? round($this->height / 2.54, 2)
            : $this->height;

        return fmod($value, 1) == 0.0 ? (int)$value : $value;
    }

}
