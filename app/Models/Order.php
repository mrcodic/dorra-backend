<?php

namespace App\Models;

use App\Enums\Order\StatusEnum;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable =[
        'order_number',
        'user_id',
        'delivery_method',
        'payment_method_id',
        'payment_status',
        'discount_amount',
        'delivery_amount',
        'tax_amount',
        'subtotal',
        'total_price',
        'status',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function totalPrice(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


     public function orderItems(): HasMany
     {
        return $this->hasMany(OrderItem::class);
    }

    public function designs()
    {
        return $this->belongsToMany(Design::class, 'order_items', 'order_id', 'design_id')
                    ->withPivot(['quantity', 'base_price', 'custom_product_price', 'total_price'])
                    ->withTimestamps();

    }

    public function orderAddress(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }


    public function pickupContact(): HasOne
    {
        return $this->hasOne(PickupContact::class);
    }



}
