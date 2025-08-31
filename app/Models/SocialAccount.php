<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'provider', 'first_name', 'last_name','email', 'provider_id'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
