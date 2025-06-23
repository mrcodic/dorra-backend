<?php

namespace App\Models;

use App\Enums\Location\DayEnum;
use App\Models\State;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\Guard;
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


<<<<<<< Updated upstream
    protected $casts = [
        'days' => DayEnum::class,
    ];
=======
>>>>>>> Stashed changes


    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

}
