<?php

namespace App\Models;

use App\Enums\Location\DayEnum;
use App\Models\State;
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


        protected $casts = [
            'days' => DayEnum::class,
        ];


        public function state()
        {
            return $this->belongsTo(State::class);
        }

}
