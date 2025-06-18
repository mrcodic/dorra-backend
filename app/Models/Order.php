<?php

namespace App\Models;

use App\Models\Design;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Enums\Order\StatusEnum;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable =[
        'order_number',
        'user_id',
        'delivery_method',
        'payment_method',
        'payment_status',
        'subtotal',
        'discount_amount',
        'delivery_amount',
        'tax_amount',
        'total_price',
        'status',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


     public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function designs()
    {
        return $this->belongsToMany(Design::class, 'order_items', 'order_id', 'design_id')
                    ->withPivot(['quantity', 'base_price', 'custom_product_price', 'total_price'])
                    ->withTimestamps();

    }

    public function OrderAddress()
    {
        return $this->hasMany(OrderAddress::class);
    }




}
