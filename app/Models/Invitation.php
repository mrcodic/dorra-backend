<?php

namespace App\Models;

use App\Enums\Invitation\StatusEnum;
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

    protected $casts = [
        'status' => StatusEnum::class,
    ];
    protected static function booted()
    {
        static::creating(function ($invitation) {
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(2);
            }
        });
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }


}
