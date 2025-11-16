<?php

namespace App\Models;

use App\Enums\Offer\TypeEnum;
use App\Notifications\OfferPlaced;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Notification;
use Spatie\Translatable\HasTranslations;

class Offer extends Model
{
    use HasTranslations;
    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'value',
        'type',
        'start_at',
        'end_at',
    ];
    protected $casts = [
        'type' => TypeEnum::class,
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($offer) {
            User::query()
                ->select('id','first_name','last_name','email')
                ->where('is_email_notifications_enabled', '=',1)
                ->whereHas('notificationTypes', function ($q) {
                    $q->where('name', 'Offers on products are placed');
                })
                ->chunkById(200, function ($users) use ($offer) {
                    Notification::send($users, new OfferPlaced($offer));
                });
        });
        parent::booted();
    }
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (int) $value . '%',

        );
    }


    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'offerable')
            ->withTimestamps();
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'offerable')
            ->withTimestamps();
    }
}
