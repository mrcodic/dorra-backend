<?php

namespace App\Models;

use App\Enums\Order\OrderTypeEnum;
use App\Models\Order;
use App\Models\Location;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{


    protected $guarded = [];
    protected $casts = [
        'shipping_method' => OrderTypeEnum::class,

    ];
    protected $appends =['name'];

    public function name(): Attribute
    {
        return Attribute::get(fn($attribute) => $attribute?->first_name . ' ' . $attribute?->last_name);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

}


