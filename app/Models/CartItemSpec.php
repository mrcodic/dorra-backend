<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemSpec extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'product_specification_id',
        'spec_option_id',
        'cart_item_id',
    ];
    protected static function booted()
    {
        static::updated(function (CartItemSpec $cartItemSpec) {
            if ($cartItemSpec->wasChanged('spec_option_id')) {
                $cartItem = $cartItemSpec->cartItem;

                if ($cartItem) {

                    $newSpecsPrice = $cartItem->specs()->sum('option.price');

                    $cartItem->update([
                        'specs_price' => $newSpecsPrice,
                        'sub_total'   => ($cartItem->product_price* $cartItem->quantity) + $newSpecsPrice,
                    ]);
                }
            }
        });
    }

    public function productSpecification(): BelongsTo
    {
        return $this->belongsTo(ProductSpecification::class);
    }

    public function productSpecificationOption(): BelongsTo
    {
        return $this->belongsTo(ProductSpecificationOption::class, 'spec_option_id');
    }

    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }
}
