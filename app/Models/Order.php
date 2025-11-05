<?php

namespace App\Models;


use App\Enums\Order\StatusEnum;
use App\Observers\OrderObserver;
use App\Services\BarcodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOne};
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'guest_id',
        'delivery_method',
        'payment_method_id',
        'payment_status',
        'discount_amount',
        'offer_amount',
        'delivery_amount',
        'tax_amount',
        'subtotal',
        'total_price',
        'status',
        'is_already_printed',
        'inventory_id',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'payment_status' => \App\Enums\Payment\StatusEnum::class,

    ];

    protected $attributes = [
        'payment_status' => \App\Enums\Payment\StatusEnum::PENDING,
    ];
    protected $appends = [
        'qr_png_url', 'qr_svg_url',
    ];


    public function getQrPngUrlAttribute(): ?string
    {
        $jobsDataUrl = route("jobs.index", ['search_value' => $this->order_number]);
        if (!$jobsDataUrl) return null;
        return app(BarcodeService::class)->savePngQR('orders', $jobsDataUrl, scale: 6);
    }

    public function getQrSvgUrlAttribute(): ?string
    {
        $jobsDataUrl = route("jobs.index", ['search_value' => $this->order_number]);
        if (!$jobsDataUrl) return null;
        return app(BarcodeService::class)->saveSvgQR('orders', $jobsDataUrl, width: 4, height: 4);

    }

    public function totalPrice(): Attribute
    {
        return Attribute::get(function ($value) {
            return fmod($value, 1) == 0.0 ? (int)$value : $value;

        });
    }
    protected function reservedPlaces(): Attribute
    {
        return Attribute::get(fn () => $this->inventories->count());
    }
    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->whereStatus($status);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'inventory_order', 'order_id', 'inventory_id')
            ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(Design::class, 'order_items');
    }


    public function orderAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class);
    }


    public function pickupContact(): HasOne
    {
        return $this->hasOne(PickupContact::class);
    }


    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
