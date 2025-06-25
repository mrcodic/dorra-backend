<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{


    protected $fillable = [
        'order_id',
        'type',
        'shipping_address_id',
        'location_id',
        'location_name',
        'address_label',
        'address_line',
        'state',
        'country',
    ];
    
    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }


     public function location()
    {
        return $this->belongsTo(Location::class); // assuming you have a locations table and model
    }

    protected $casts = [
    'shipping_method' => ShippingMethodEnum::class,

];

}


