<?php

namespace App\Models;

// use Illuminate\Interfaces\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'password_updated_at',
        'country_code_id',
        'status',
        'is_email_notifications_enabled',
        'is_mobile_notifications_enabled',
        'last_login_ip',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $attributes = [
        'status' => 1,
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_updated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getFirstMedia('users')
        );
    }

    public function countryCode(): BelongsTo
    {
        return $this->belongsTo(CountryCode::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function notificationTypes()
    {
        return $this->belongsToMany(NotificationType::class)
            ->withPivot('enabled')
            ->withTimestamps();
    }




}
