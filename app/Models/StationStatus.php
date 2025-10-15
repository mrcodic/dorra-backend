<?php

namespace App\Models;

use App\Observers\StationStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(StationStatusObserver::class)]
class StationStatus extends Model
{
    protected $fillable = ['station_id', 'code', 'name', 'sequence',
        'resourceable_id',
        'resourceable_type',
        'is_terminal'];

    public function resourceable()
    {
        return $this->morphTo();
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
