<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable implements HasMedia
{
    use InteractsWithMedia, HasRoles;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'password_updated_at',
        'status',
    ];
    protected function casts(): array
    {
        return [
            'password_updated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
