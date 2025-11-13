<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Admin extends Authenticatable implements HasMedia
{
    use InteractsWithMedia, HasRoles,Notifiable;

    protected $guard_name = 'web';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'password_updated_at',
        'status',
    ];
    protected $attributes = [
        'status' => 1,
    ];

    public function name(): Attribute
    {
        return Attribute::get(fn() => $this->first_name . ' ' . $this->last_name);
    }

    public function recentMockups()
    {
        return $this->belongsToMany(Mockup::class, 'admin_mockup_usages')
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc')
            ->distinct();
    }

    protected function casts(): array
    {
        return [
            'password_updated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getFirstMedia('admins')
        );
    }
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

}
