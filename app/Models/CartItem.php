<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy(CartItemObserver::class)]
class CartItem extends Model
{
    protected $fillable = [
        'itemable_id',
        'itemable_type',
        'cart_id',
        'cartable_id',
        'cartable_type',
        'specs_price',
        'sub_total',
        'product_price',
        'product_price_id',
        'quantity',
        'color'
    ];
    protected $table = 'cart_items';
    protected $appends = ['sub_total_after_offer','offer_amount'];

    public function getSubTotalAfterOfferAttribute(): float
    {
        $sub = (float) $this->sub_total;
        $val = (float) optional($this->cartable->lastOffer)->getRawOriginal('value');
        return $val > 0 ? $sub * (1 -( $val / 100)) : $sub;
    }
    public function getOfferAmountAttribute(): float
    {
        $sub = (float) $this->sub_total;
        $val = (float) optional($this->cartable->lastOffer)->getRawOriginal('value');
        return $val > 0 ? $sub * $val / 100 : 0;
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function cartable(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(CartItemSpec::class, 'cart_item_id');
    }

}
