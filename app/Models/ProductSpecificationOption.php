<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ProductSpecificationOption extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;
    protected $fillable = [
        'product_specification_id',
        'value',
        'price',
    ];

    protected $translatable = ['value'];
    protected static function booted()
    {
        static::updated(function (ProductSpecificationOption $specificationOption) {
            if ($specificationOption->wasChanged('price'))
            {
                CartItemSpec::where('spec_option_id', $specificationOption->id)->get()
                    ->each(function ($item) use ($specificationOption) {
                        $item->update([
                            'spec_option_id' => $specificationOption->id,
                        ]);
                    });

            }

        });
    }
    public function image(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMedia('productSpecificationOptions'));
    }

    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }
}
