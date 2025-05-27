<?php

namespace App\Models;

use App\Observers\DesignObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(DesignObserver::class)]
class Design extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia;

    protected $fillable =[
        'cookie_id',
        'user_id',
        'template_id',
        'design_data',
        'current_version',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }


}
