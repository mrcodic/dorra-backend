<?php

namespace App\Models;


use App\Enums\OrientationEnum;
use App\Observers\DesignObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,
    BelongsToMany,
    HasMany,
    HasOneThrough,
    MorphMany,
    MorphTo,
    MorphToMany
};
use Spatie\Translatable\HasTranslations;

#[ObservedBy(DesignObserver::class)]
class Design extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, HasTranslations, softDeletes;

    public $translatable = ['name', 'description'];

    protected $fillable = [
        'cookie_id',
        'user_id',
        'guest_id',
        'template_id',
        'dimension_id',
        'design_data',
        'design_back_data',
        'current_version',
        'product_price_id',
        'quantity',
        'name',
        'description',
        'designable_id',
        'designable_type',
        'orientation',
        'price',
    ];
    protected $attributes = [
        'quantity' => 1,
        'current_version' => 0
    ];
    protected $casts = [
        'orientation' => OrientationEnum::class,];

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
        return $this->morphToMany(Team::class, 'teamable')->withTimestamps();
    }

    public function quantity(): Attribute
    {

        return Attribute::get(function ($value) {
            return $this->productPrice ? $this->productPrice->quantity : $value;
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function categories()
    {
        return $this->morphedByMany(Category::class, 'designable', 'designables')
            ->withTimestamps();
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'designable', 'designables')
            ->withTimestamps();
    }

    public function product()
    {
        return $this->designable()->where('designable_type', Product::class);
    }

    public function designable(): MorphTo
    {
        return $this->morphTo();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }

    public function option()
    {

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

    public function specifications()
    {
        return $this->belongsToMany(
            \App\Models\ProductSpecification::class,
            'design_specifications',
            'design_id',
            'product_spec_id'
        )
            ->withPivot('option_id')
            ->using(DesignSpecification::class)
            ->withTimestamps();
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
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

    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot([
            'sub_total', 'total_price'
        ]);
    }

    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }
    public function getFrontImageUrl(): string
    {
        return $this->getFirstMediaUrl('designs') ?:  asset('images/default-product.png');
    }
    public function getBackImageUrl(): string
    {
        return $this->getFirstMediaUrl('back_designs') ?:  asset('images/default-product.png');
    }
    public function getNoneImageUrl(): string
    {
        return $this->getFirstMediaUrl('designs') ?:  asset('images/default-product.png');
    }

    public function getImageUrlForType(string $type): array|string
    {
        $type = strtolower($type);
        return match ($type) {
            'front' => $this->getFrontImageUrl(),
            'back'  => $this->getBackImageUrl(),
            'both'  => [
                'front' => $this->getFrontImageUrl(),
                'back'  => $this->getBackImageUrl(),
            ],
            default => $this->getNoneImageUrl(),
        };
    }
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {

        $this->addMediaConversion('front_png')
            ->format('png')
            ->performOnCollections('templates');

        $this->addMediaConversion('front_jpg')
            ->format('jpg')
            ->performOnCollections('templates');

        $this->addMediaConversion('front_svg')
            ->format('svg')
            ->performOnCollections('templates');

        $this->addMediaConversion('front_pdf')
            ->format('pdf')
            ->performOnCollections('templates');


        $this->addMediaConversion('back_png')
            ->format('png')
            ->performOnCollections('back_templates');

        $this->addMediaConversion('back_jpg')
            ->format('jpg')
            ->performOnCollections('back_templates');
        $this->addMediaConversion('back_svg')
            ->format('svg')
            ->performOnCollections('templates');

        $this->addMediaConversion('back_pdf')
            ->format('pdf')
            ->performOnCollections('templates');
    }
    
}
