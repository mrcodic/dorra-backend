<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobEvent extends Model
{
    protected $fillable = [
        'station_id',
        'job_ticket_id',
        'admin_id',
        'action',
        'meta_data',
    ];
    protected $casts = [
        'meta_data' => 'array',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function jobTicket(): BelongsTo
    {
        return $this->belongsTo(JobTicket::class);
    }
}
