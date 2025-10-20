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
        static::created(fn ($item) => $item->recalculateCartItem());
        static::deleted(fn ($item) => $item->recalculateCartItem());
        static::updated(function($item){
            $item->recalculateCartItem();
            if ($item->cartItem->user_id) {
                $item->cartItem->expires_at = now()->addHours(
                    config('cart.user_expiration_hours', 24)
                );
                $item->cartItem->saveQuietly();
            } elseif ($item->cartItem->guest_id) {
                $item->cartItem->expires_at = now()->addMinutes(
                    config('cart.guest_expiration_minutes', 60)
                );
                $item->cartItem->saveQuietly();
            } else {
                $item->cartItem->expires_at = now()->addHour();
                $item->cartItem->saveQuietly();
            }

        });
    }

    public function recalculateCartItem()
    {
        $cartItem = $this->cartItem;
        if ($cartItem) {
            $newSpecsPrice = $cartItem->specs
                ->map(fn ($spec) => $spec->productSpecificationOption?->price ?? 0)
                ->sum();

            $cartItem->update([
                'specs_price' => $newSpecsPrice,
                'sub_total'   => $cartItem->cartable->has_custom_prices
                    ? $cartItem->product_price + $newSpecsPrice
                    : ($cartItem->product_price * $cartItem->quantity) + $newSpecsPrice,
            ]);
        }
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
