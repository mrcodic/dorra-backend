<?php

namespace App\Models;

use App\Enums\Payment\StatusEnum;
use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ObservedBy(TransactionObserver::class)]

class Transaction extends Model
{
    protected $guarded = [];
    protected $casts = [
        'payment_status' => StatusEnum::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
