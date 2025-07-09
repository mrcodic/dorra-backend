<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use App\Models\Design;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
