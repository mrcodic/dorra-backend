<?php

namespace App\Models;

use App\Models\Order;
use App\Observers\DesignObserver;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

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


    public function order()
{
    return $this->belongsTo(Order::class);
}

}
