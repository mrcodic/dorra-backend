<?php

namespace App\Models;

use App\Enums\Template\StatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Template extends Model implements HasMedia
{
    use HasUuids,HasTranslations, InteractsWithMedia;
    public $translatable = ['name','description'];

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
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecification::class)->withTimestamps();
    }



}
