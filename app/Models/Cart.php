<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        "user_id",
        "guest_id",
        "price",
    ];

    public function price(): Attribute
    {
        return Attribute::get(function ($value){
           return fmod($value, 1) == 0.0 ? (int)$value : $value;
        });
    }
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    public function addItem(Model $itemable,$quantity,$specsSum,$productPrice,$subTotal, ?Product $product = null,): CartItem
    {
        return $this->items()->create([
            'itemable_id' => $itemable->id,
            'itemable_type' => get_class($itemable),
            'product_id' => $product?->id,
            'sub_total' => $subTotal,
            'specs_price' => $specsSum,
            'product_price' => $productPrice,
            'quantity' => $quantity,
        ]);
    }
    public function removeItem(CartItem $item)
    {
        return $item->delete();
    }
    public function clear()
    {
        return $this->items()->delete();
    }
    public function totalItems()
    {
        return $this->items()->count();
    }

    public function itemsByType(string $class)
    {
        return $this->items()->where('itemable_type', $class)->get();
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
