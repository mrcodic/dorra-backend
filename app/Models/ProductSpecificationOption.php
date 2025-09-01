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
        $callback = function (ProductSpecificationOption $specificationOption) {
            if ($specificationOption->wasChanged('price'))
            {
                dd("safsd");
                CartItemSpec::where('spec_option_id', $specificationOption->id)
                    ->with('cartItem.specs.productSpecificationOption')
                    ->get()
                    ->each(function ($item) {
                        $cartItem = $item->cartItem;

                        if ($cartItem) {
                            $newSpecsPrice = $cartItem->specs
                                ->map(fn ($spec) => $spec->productSpecificationOption?->price ?? 0)
                                ->sum();

                            $cartItem->update([
                                'specs_price' => $newSpecsPrice,
                                'sub_total'   => $cartItem->product->has_custom_prices ?
                                    $cartItem->product_price + $newSpecsPrice
                                    : ($cartItem->product_price * $cartItem->quantity) + $newSpecsPrice
                                ,
                            ]);
                        }
                    });

            }

        };
        static::updated($callback);
        static::deleted($callback);
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
