<?php

namespace App\Models;

use App\Models\Design;
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


    public function designs()
    {
        return $this->hasMany(Design::class);
    }


    public function OrderAddress()
    {
        return $this->hasMany(OrderAddress::class);
    }

}
