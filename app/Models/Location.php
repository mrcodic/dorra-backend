<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
class Location extends Model
{
    protected $fillable = [
        'name',
        'state_id',
        'address_line',
        'link',
        'latitude',
        'longitude',
        'days',
        'available_time',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

}
