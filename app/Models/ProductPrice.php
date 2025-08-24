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
        static::created(function (ProductPrice $productPrice) {
            dd($productPrice->product->carts->isNotEmpty());
            if ($productPrice->product->carts->isNotEmpty()) {
                $product = $productPrice->product;
                CartItem::where('product_id', $product->id)
                    ->with('cart')
                    ->get()
                    ->each(function ($item) use ($productPrice) {
                        $productPriceValue = $productPrice->price;
                        $quantity = $productPrice->quantity;
                        $specsPrice = $item->specs_price ?? 0;
                        $discount = $item->cart?->discount_amount ?? 0;

                        $item->update([
                            'product_price' => $productPriceValue,
                            'sub_total'     => ($productPriceValue * $quantity) + $specsPrice - $discount,
                        ]);
                    });
            }
        });

    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
