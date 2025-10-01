<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = [
        'code',
        'name',
        'workflow_order',
        'is_terminal',
        'requires_operator',
    ];

    public function jobTickets(): HasMany
    {
        return $this->hasMany(JobTicket::class);
    }
}
