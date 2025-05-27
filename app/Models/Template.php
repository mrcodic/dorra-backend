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

    ];
    public $incrementing = false;
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
