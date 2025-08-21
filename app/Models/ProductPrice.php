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
        static::updated(function (ProductPrice $productPrice) {
            if ($productPrice->wasChanged('price') || $productPrice->wasChanged('quantity'))
            {
                $product = $productPrice->product;
                CartItem::where('product_id', $product->id)->get()
                    ->each(function ($item) use ($product, $productPrice) {
                        $item->update([
                            'product_price' => $productPrice->price,
                            'quantity' => $productPrice->quantity,
                            'sub_total'     => $productPrice->price + $item->specs_price - $item->cart->discount_amount,
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
