<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockupSideSetting extends Model
{
    protected $fillable = [
        'mockup_id',
        'side',
        'is_active',
        'warp_points',
        'render_presets',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'warp_points' => 'array',
        'render_presets' => 'array',
    ];

    public function mockup(): BelongsTo
    {
        return $this->belongsTo(Mockup::class);
    }
}
