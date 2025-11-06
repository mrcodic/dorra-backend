<?php

namespace App\Models;

use App\Models\State;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ShippingAddress extends Model
{
    protected $fillable = ['label', 'line', 'user_id', 'guest_id', 'zone_id'];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
