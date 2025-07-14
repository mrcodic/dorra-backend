<?php

namespace App\Models;

use App\Enums\Payment\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];
    protected $casts = [
        'payment_status' => StatusEnum::class,
    ];
}
