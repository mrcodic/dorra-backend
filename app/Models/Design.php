<?php

namespace App\Models;

use App\Observers\DesignObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

#[ObservedBy(DesignObserver::class)]
class Design extends Model
{
    use HasUuids;
    protected $fillable =[
        'cookie_id',
        'user_id',
        'template_id',
        'design_data',
        'design_image',
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
    public function designImage(): Attribute
    {
        return Attribute::get(function ($value){
            return  asset($value);
        });
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }
}
