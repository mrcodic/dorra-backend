<?php

namespace App\Models;

use App\Enums\Offer\TypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Translatable\HasTranslations;

class Offer extends Model
{
    use HasTranslations;
    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'value',
        'type',
        'start_at',
        'end_at',
    ];
    protected $casts = [
        'type' => TypeEnum::class,
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'offerable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'offerable');
    }
}
