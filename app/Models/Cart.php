<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        "user_id",
        "guest_id",
        "price",
        "discount_amount",
        "discount_code_id"

    ];

    public function price(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;
        });
    }

    public function addItem(Model $itemable, $quantity, $specsSum, $productPrice, $productPriceId, $subTotal, $cartable_id, $cartable_type): CartItem
    {
        return $this->items()->create([
            'itemable_id' => $itemable->id,
            'itemable_type' => get_class($itemable),
            'cartable_id' => $cartable_id,
            'cartable_type' => $cartable_type,
            'product_price_id' => $productPriceId,
            'sub_total' => $subTotal,
            'specs_price' => $specsSum,
            'product_price' => $productPrice,
            'quantity' => $quantity ?? 1,
        ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function totalItems()
    {
        return $this->items()->count();
    }


    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
