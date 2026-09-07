<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaDeleteLog extends Model
{
    protected $fillable = [
        'media_id',
        'deleted_by',
        'ip_address',
        'user_agent',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id')
            ->withTrashed();
    }
    public function deletedBy(): MorphTo
    {
        return $this->morphTo();
    }
}

