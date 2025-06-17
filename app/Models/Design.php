<?php

namespace App\Models;

use App\Models\Order;
use App\Observers\DesignObserver;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasOneThrough};

#[ObservedBy(DesignObserver::class)]
class Design extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia;

    protected $fillable = [
        'cookie_id',
        'user_id',
        'order_id',
        'template_id',
        'design_data',
        'current_version',
        'product_price_id',
        'quantity',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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


    public function versions(): HasMany
    {
        return $this->hasMany(DesignVersion::class);
    }


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductSpecificationOption::class,'design_product_specification','spec_option_id')
            ->using(DesignProductSpecification::class)
            ->withTimestamps();
    }
}
