<?php

namespace App\Models;

use App\Enums\Template\StatusEnum;
use App\Enums\Template\TypeEnum;
use App\Enums\Template\UnitEnum;
use App\Observers\TemplateObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(TemplateObserver::class)]
class Template extends Model implements HasMedia
{
    use HasUuids, HasTranslations, InteractsWithMedia;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'status',
        'product_id',
        'design_data',
        'type',
        'description',
        'unit',
        'height',
        'width',
    ];
    protected $casts = [
        'status' => StatusEnum::class,
        'type' => TypeEnum::class,
        'unit' => UnitEnum::class,
    ];

    protected $attributes = [
        'status' => StatusEnum::DRAFTED,
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecification::class)->withTimestamps();
    }


    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl('templates') ?: "";

    }

    public function height(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }

    public function width(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
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


    public function scopeLive(Builder $builder): Builder
    {
        return $builder->where('status', StatusEnum::LIVE);
    }




}
