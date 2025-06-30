<?php

namespace App\Models;

// use Illuminate\Interfaces\Auth\MustVerifyEmail;
use App\Models\ShippingAddress;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, MorphToMany};

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia, HasRoles;

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
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            $user->last_login_ip = request()->ip();
            $user->last_login_at = now();
        });
        parent::booted();
    }

    public function status(): Attribute
    {
        return Attribute::get(fn(?bool $value) => $value == 0 ? 'Blocked' : 'Active');
    }
    public function getNameAttribute(): string
    {
        return $this->first_name . ' '. $this->last_name;
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getFirstMedia('users')
        );
    }

    public function name(): Attribute
    {
       return Attribute::get(fn()=> $this->first_name.' '.$this->last_name);
    }

    public function countryCode(): BelongsTo
    {
        return $this->belongsTo(CountryCode::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class);
    }

    public function notificationTypes(): BelongsToMany
    {
        return $this->belongsToMany(NotificationType::class)
            ->withPivot('enabled')
            ->withTimestamps();
    }

    public function savedProducts(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'savable','saves')->withTimestamps();
    }

/*    public function savedProjects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'savable','saves')->withTimestamps();
    }*/

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function designs(): HasMany
    {
        return $this->hasMany(Design::class);
    }


}
