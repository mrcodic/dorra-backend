<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function scopeUserId(Builder $builder, $value): Builder
    {

         return $builder->whereHas('users', function ($builder) use ($value) {
            $builder->where('users.id', $value);
        });
    }
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('enabled')
            ->withTimestamps();
    }


}
