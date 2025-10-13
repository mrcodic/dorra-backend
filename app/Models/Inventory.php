<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Inventory extends Model
{
    protected $fillable = ['name', 'number', 'parent_id', 'is_available'];
    protected $attributes = [
        'is_available' => 1,
    ];

    public static function booted()
    {
        static::deleting(function ($inventory) {
            $inventory->children()->delete();
        });
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Inventory::class, 'parent_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('parent_id')
            ->where('is_available', 1);
    }
    public function scopeUnAvailable(Builder $query): Builder
    {
        return $query
            ->whereNotNull('parent_id')
            ->where('is_available', 0);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

}
