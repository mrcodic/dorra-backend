<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable =[
        'team_id',
        'design_id',
        'email',
        'expires_at',
    ];
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
