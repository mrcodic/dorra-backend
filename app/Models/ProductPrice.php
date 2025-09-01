<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'price',
    ];
    protected static function booted()
    {
        $callback = function (ProductPrice $productPrice) {
            if ($productPrice->product->carts->isNotEmpty()) {
                $product = $productPrice->product;
                CartItem::where('product_id', $product->id)
                    ->with('cart')
                    ->get()
                    ->each(function ($item) use ($productPrice) {
                        $productPriceValue = $productPrice->price;
                        $specsPrice = $item->specs->sum(
                            fn($item) => $item->productSpecificationOption?->price ?? 0
                        );
                        if ($item->product->has_custom_prices) {
                            $subTotal = ($item->productPrice?->price ?? $item->product_price)
                                + ($specsPrice ?: $item->specs_price);
                        } else {
                            $subTotal = (
                                    ($item->product->base_price ?? $item->product_price)
                                    + ($specsPrice ?: $item->specs_price)
                                ) * $item->quantity;
                        }

                        $item->sub_total = $subTotal;
                        $item->save();

                        if ((float)$productPriceValue < (float)$item->cart?->discount_amount && (float)$productPriceValue == $item->product_price)
                        {

                            $item->cart->update([
                                'discount_amount' => 0,
                                'discount_code_id' => null,
                            ]);
                        }
                    });
            }
        };
        static::saved($callback);
        static::deleted(function (ProductPrice $productPrice) {
            if ($productPrice->product->carts->isNotEmpty()) {
                $product = $productPrice->product;
                CartItem::where('product_id', $product->id)
                    ->delete();
            }

        });


    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
