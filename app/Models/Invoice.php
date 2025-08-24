<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;


class Invoice extends Model
{
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function designs(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Design::class, 'designable');
  }
}
