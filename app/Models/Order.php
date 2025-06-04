<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
