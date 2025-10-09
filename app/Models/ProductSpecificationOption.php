<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
                                'sub_total'   => $cartItem->cartable->has_custom_prices ?
                                    $cartItem->product_price + $newSpecsPrice
                                    : ($cartItem->product_price * $cartItem->quantity) + $newSpecsPrice
                                ,
                            ]);
                        }
                    });


        };
        static::updated($callback);
        static::deleting(function ($specOption) {
            $specOption->cartItemSpecs->each->delete();
        });
    }

    public function cartItemSpecs(): HasMany
    {
        return $this->hasMany(CartItemSpec::class,'spec_option_id');
    }
    public function image(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMedia('productSpecificationOptions') ?: $this->getFirstMedia('categorySpecificationOptions'));
    }

    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }
}
