<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Inventory extends Model
{
    protected $fillable = ['name', 'number','parent_id','is_available'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Inventory::class, 'parent_id');
    }

    public function scopeAvailable()
    {
        return $this->whereIsAvailable(true);
    }
    public function scopeUnAvailable()
    {
        return $this->whereIsAvailable(false);
    }
}
