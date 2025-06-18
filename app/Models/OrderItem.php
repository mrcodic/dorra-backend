<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Design;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'design_id',
        'quantity',
        'base_price',
        'custom_product_price',
        'total_price',
    ];




    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
