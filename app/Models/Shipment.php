<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable =[
        'provider',
        'provider_order_id',
        'tracking_number',
        'status',
        'meta'
    ];

  protected  $casts = [
        'meta' => 'array'
        ];

}
