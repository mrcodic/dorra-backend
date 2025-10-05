<?php

namespace App\Models;

use App\Enums\JobTicket\PriorityEnum;
use App\Enums\JobTicket\StatusEnum;
use App\Observers\JobTicketObserver;
use App\Services\BarcodeService;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};

#[ObservedBy(JobTicketObserver::class)]
class JobTicket extends Model
{
    protected $fillable = [
        'code',
        'order_item_id',
        'station_id',
        'current_status_id',
        'specs',
        'priority',
        'due_at',
        'status',
    ];
    protected $casts = [
        'specs' => 'array',
        'status' => StatusEnum::class,
        'priority' => PriorityEnum::class,
        'due_at' => 'date',
    ];
    protected $attributes = [
        'status' => StatusEnum::PENDING,

    ];

    protected $appends = [
        'barcode_png_url', 'barcode_svg_url',
    ];

    public function getBarcodePngUrlAttribute(): ?string
    {
        if (!$this->code) return null;
        return app(BarcodeService::class)->savePng1D($this->code);
    }

    public function getBarcodeSvgUrlAttribute(): ?string
    {
        if (!$this->code) return null;
        return app(BarcodeService::class)->saveSvg1D($this->code);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(StationStatus::class, 'current_status_id')
            ->withDefault(['name' => StatusEnum::PENDING->label()]);
    }

    public function jobEvents(): HasMany
    {
        return $this->hasMany(JobEvent::class);
    }
}
