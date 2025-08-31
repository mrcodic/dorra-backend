<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{

    protected $fillable = [
        'itemable_id',
        'itemable_type',
        'order_id',
        'product_id',
        'specs_price',
        'sub_total',
        'product_price',
        'product_price_id',
        'quantity'
    ];
    public function totalPrice(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function specs(): HasMany
    {
        return $this->hasMany(OrderItemSpec::class,'order_item_id');
    }
}
