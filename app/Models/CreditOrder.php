<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditOrder extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'credits',
        'amount',
        'status',
        'plan_id'
    ];
}
