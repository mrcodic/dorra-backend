<?php

namespace App\Models;

use App\Enums\Setting\SocialEnum;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $fillable = [
        'platform',
        'url',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'platform' => SocialEnum::class,
    ];
}
