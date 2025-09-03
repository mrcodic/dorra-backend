<?php

namespace App\Models;


use App\Observers\DesignObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOneThrough, MorphMany, MorphToMany};
use Spatie\Translatable\HasTranslations;

#[ObservedBy(DesignObserver::class)]
class Design extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, softDeletes;
//    public $translatable = ['name', 'description'];


    protected $fillable = [
        'cookie_id',
        'user_id',
        'guest_id',
        'template_id',
        'design_data',
        'design_back_data',
        'current_version',
        'product_price_id',
        'quantity',
        'name',
        'description',
        'product_id'
    ];
    protected $attributes = [
        'quantity' => 1,
        'current_version' => 0
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
    public function users()
    {
        return $this->morphedByMany(User::class, 'designable', 'designables')->withTimestamps();
    }

    public function saves(): MorphMany
    {
        return $this->morphMany(Save::class, 'savable');
    }

    public function folders()
    {
        return $this->morphedByMany(Folder::class, 'designable', 'designables')->withTimestamps();
    }
    public function teams(): MorphToMany
    {
        return $this->morphToMany(Team::class,'teamable')->withTimestamps();
    }

    public function quantity(): Attribute
    {

        return Attribute::get(function ($value) {
            return $this->productPrice ? $this->productPrice->quantity : $value;
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault(['name' => '']);
    }


    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }


    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items', 'design_id', 'order_id')
            ->withPivot(['quantity', 'base_price', 'custom_product_price', 'total_price'])
            ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class);
    }

    public function specifications(): MorphMany
    {
        return $this->morphMany(CustomizableProductSpecification::class, 'customizable');
    }

    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot([
            'sub_total', 'total_price'
        ]);
    }



    public function invoices()
    {
        return $this->morphedByMany(Invoice::class, 'designable', 'designables')->withTimestamps();
    }

    public function isAddedToCart(): bool
    {
        $userId = auth('sanctum')->id();
        $cookieId = request()->cookie('cookie_id');
        $guestId = \App\Models\Guest::where('cookie_value', $cookieId)->value('id');

        return $this->cartItems()
            ->where(function ($q) use ($userId, $guestId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }

                if ($guestId) {
                    $q->orWhere('guest_id', $guestId);
                }
            })
            ->exists();
    }


}
