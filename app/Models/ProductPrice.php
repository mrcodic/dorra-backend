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
            if ($productPrice->wasChanged('price'))
            {
                $product = $productPrice->product->id;
                CartItem::where('product_id', $product)->get()
                    ->each(function ($item) use ($product) {
                        $item->update([
                            'product_price' => $product->price,
                            'sub_total'     => $product->price + $item->specs_price - $item->cart->discount_amount,
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
