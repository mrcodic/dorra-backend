<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationStatus extends Model
{
    protected $fillable = ['station_id','code','name','sequence','is_terminal'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
