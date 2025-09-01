<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class OrderItemSpec extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'spec_name',
        'option_name',
        'option_price',
        'order_item_id',
        'spec_option_id',
        'product_specification_id',
    ];
    public function productSpecification(): BelongsTo
    {
        return $this->belongsTo(ProductSpecification::class);
    }

    public function productSpecificationOption(): BelongsTo
    {
        return $this->belongsTo(ProductSpecificationOption::class, 'spec_option_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
