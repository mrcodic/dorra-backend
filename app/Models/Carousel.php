<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Carousel extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;
    public $translatable = ['title', 'subtitle'];

    protected $fillable =[
        'title',
        'subtitle',
        'product_id',
        'category_id',
        'title_color',
        'subtitle_color'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
