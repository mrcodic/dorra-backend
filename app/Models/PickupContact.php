<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupContact extends Model
{
    protected $fillable = [
        'order_id',
        'first_name',
        'last_name',
        'email',
        'phone',
    ];
}
