<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Otp extends Model
{
    protected $fillable = [
        'otpable_id',
        'otpable_type',
        'identifier',
        'otp',
        'type',
        'expires_at',
    ];
    public function otpable(): MorphTo
    {
        return $this->morphTo();
    }
}
