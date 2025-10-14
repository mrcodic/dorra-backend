<?php

namespace App\Models;

use App\Enums\Offer\TypeEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

//    protected function value(): Attribute
//    {
//        return Attribute::make(
//            get: fn ($value) => (int) $value . '%',
//
//        );
//    }
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'offerable')
            ->withTimestamps();
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'offerable')
            ->withTimestamps();
    }
}
