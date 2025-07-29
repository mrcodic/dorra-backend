<?php

namespace App\Models;

use App\Observers\CartItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
#[ObservedBy(CartItemObserver::class)]
class CartItem extends Model
{
    protected $fillable = [
        'itemable_id',
        'itemable_type',
        'cart_id',
        'product_id',
        'specs_price',
        'sub_total',
        'product_price',
        'quantity'
    ];
    protected $table = 'cart_items';

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function specs(): HasMany
    {
        return $this->hasMany(CartItemSpec::class,'cart_item_id');
    }

}
