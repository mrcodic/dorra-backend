<?php

namespace App\Models;

use App\Enums\JobTicket\PriorityEnum;
use App\Enums\JobTicket\StatusEnum;
use App\Observers\JobTicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ObservedBy(JobTicketObserver::class)]

class JobTicket extends Model
{
    protected $fillable = [
        'code',
        'order_item_id',
        'station_id',
        'specs',
        'priority',
        'due_at',
        'status',
    ];
    protected $casts = [
        'specs' => 'array',
        'status' => StatusEnum::class,
        'priority' => PriorityEnum::class,
        'due_at' => 'datetime',
    ];
    protected $attributes = [
        'status' => StatusEnum::PENDING,

    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
