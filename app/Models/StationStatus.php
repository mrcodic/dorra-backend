<?php

namespace App\Models;

use App\Observers\StationStatusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(StationStatusObserver::class)]
class StationStatus extends Model
{
    protected $fillable = ['station_id','code','name','sequence',
        'parent_id',
        'job_ticket_id',
        'is_terminal'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
