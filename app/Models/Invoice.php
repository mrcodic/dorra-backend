<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;


class Invoice extends Model
{
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function totalClients(): int
    {
        return (int) static::query()
            ->join('orders', 'orders.id', '=', 'invoices.order_id')
            ->where(function ($q) {
                $q->whereNotNull('orders.user_id')
                    ->orWhereNotNull('orders.guest_id');
            })
            ->selectRaw("
                COUNT(DISTINCT
                    CASE
                        WHEN orders.user_id IS NOT NULL THEN CONCAT('u:', orders.user_id)
                        ELSE CONCAT('g:', orders.guest_id)
                    END
                ) AS cnt
            ")
            ->value('cnt');
    }
    public function designs(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable');
  }
}
