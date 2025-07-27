<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class OrderItemSpec extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'spec_name',
        'option_name',
        'option_price',
        'order_item_id',
    ];

}
