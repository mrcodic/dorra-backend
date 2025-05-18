<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Translatable\HasTranslations;

class Template extends Model
{
    use HasUuids,HasTranslations;
    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'status',
        'product_id',
        'json_data',
        'preview_png',
        'source_svg',
    ];
    public $incrementing = false;
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
