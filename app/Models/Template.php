<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Template extends Model implements HasMedia
{
    use HasUuids,HasTranslations, InteractsWithMedia;
    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'status',
        'product_id',
        'design_data',
        'preview_image',
        'source_design_svg',
    ];
    public $incrementing = false;
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function previewImage(): Attribute
    {
        return Attribute::get(function ($value){
           return   $this->getFirstMediaUrl()  ?  $this->getFirstMediaUrl() : null;
        });
    }

    public function sourceDesignSvg(): Attribute
    {
        return Attribute::get(function ($value){
           return $value ?   asset($value) : null;
        });
    }
}
