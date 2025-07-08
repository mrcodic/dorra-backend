<?php

namespace App\Models;


use App\Observers\DesignObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOneThrough};
use Spatie\Translatable\HasTranslations;

#[ObservedBy(DesignObserver::class)]
class Design extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, HasTranslations;
    public $translatable = ['name', 'description'];

    protected $fillable = [
        'cookie_id',
        'user_id',
        'order_id',
        'template_id',
        'design_data',
        'current_version',
        'product_price_id',
        'quantity',
        'name',
        'description',
        'height',
        'width',
        'unit',
        'product_id'
    ];
    protected $attributes = [
        'quantity' => 1,
        'current_version' => 0
    ];
    public function designable()
    {
        return $this->morphedByMany(User::class, 'designable', 'designables');
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

    public function product(): HasOneThrough
    {
        return $this->hasOneThrough(
            Product::class,
            Template::class,
            'id',
            'id',
            'template_id',
            'product_id'
        );
    }
    public function directProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
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

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecification::class)
            ->using(DesignProductSpecification::class)
            ->withPivot('spec_option_id')->withTimestamps();
    }

    public function cartItems(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')->withPivot([
            'sub_total', 'total_price'
        ]);
    }


    public function getTotalPriceAttribute(): float
    {
        $this->load('productPrice');
        $specOptions = $this->options()->select('price')->get();
        $specTotalPrice = $specOptions->sum('price');
        $productPrice = $this->productPrice->price ?? $this->product?->base_price * $this->quantity;
        return $specTotalPrice + $productPrice;
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecificationOption::class, 'design_product_specification',
            'design_id',
            'spec_option_id')
            ->using(DesignProductSpecification::class)
            ->withTimestamps();
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'design_user')->withTimestamps();
    }
}
