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

    public function client(): Attribute
    {
        return Attribute::get(fn () => $this->order?->user ?? $this->order?->guest);
    }

    public function clientCount(): int
    {
        return $this->order?->user_id || $this->order?->guest_id ? 1 : 0;
    }

    public function designs(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable');
  }
}
