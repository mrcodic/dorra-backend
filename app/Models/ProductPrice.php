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
            if ($productPrice->product->carts->isNotEmpty()) {
                $product = $productPrice->product;
                CartItem::where('product_id', $product->id)
                    ->with('cart')
                    ->get()
                    ->each(function ($item) use ($productPrice) {
                        $productPriceValue = $productPrice->price;

                        if ($productPriceValue < $item->cart?->discount_amount)
                        {
                            dd($productPriceValue,$item->cart?->discount_amount);
                            
                            $item->cart->update([
                                'discount_amount' => 0,
                                'discount_code_id' => null,
                            ]);
                        }
                    });
            }
        });

    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
