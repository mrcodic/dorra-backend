<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPromptTemplate extends Model
{
    protected $fillable = [
        'name',
        'system_prompt',
        'template',
        'negative_rules',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
