<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
    protected $attributes = [
        'status' => 1,
    ];

    public function name(): Attribute
    {
        return Attribute::get(fn()=> $this->first_name.' '.$this->last_name);
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getFirstMedia('admins')
        );
    }

}
