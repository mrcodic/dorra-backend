<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CartItemSpec extends Model
{
    protected $fillable = [
        'spec_name',
        'option_name',
        'option_price',
        'cart_item_id',
    ];
    public $timestamps = false;

}
