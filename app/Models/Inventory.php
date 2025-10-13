<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Inventory extends Model
{
    protected $fillable = ['name', 'number', 'parent_id', 'is_available'];
    protected $attributes = [
        'is_available' => 1,
    ];

    protected static function booted(): void
    {

        static::deleting(function (Inventory $inventory) {

            $inventory->orders()->update(['inventory_id' => null]);
            $inventory->children()->each(function (Inventory $child) use ($inventory) {
                if (method_exists($inventory, 'isForceDeleting') && $inventory->isForceDeleting()) {
                    $child->forceDelete();
                } else {
                    $child->delete();
                }
            });
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

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'inventory_order', 'inventory_id', 'order_id')
            ->withTimestamps();
    }

}
