<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemSpec extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'product_specification_id',
        'spec_option_id',
        'cart_item_id',
    ];

    public function productSpecification(): BelongsTo
    {
        return $this->belongsTo(ProductSpecification::class);
    }

    public function productSpecificationOption(): BelongsTo
    {
        return $this->belongsTo(ProductSpecificationOption::class, 'spec_option_id');
    }

    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }
}
