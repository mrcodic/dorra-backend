<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ShippingAddress extends Model
{
    protected $fillable =['label', 'line', 'state_id', 'user_id'];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
