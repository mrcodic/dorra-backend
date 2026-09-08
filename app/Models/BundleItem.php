<?php

namespace App\Models;

use App\Enums\Bundle\DiscountTypeEnum;
use App\Enums\Bundle\ItemRoleEnum;
use App\Enums\Bundle\QuantityRuleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BundleItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bundle_id',
        'itemable_type',
        'itemable_id',
        'role',
        'quantity_rule',
        'quantity',
        'price_id',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'role' => ItemRoleEnum::class,
            'quantity_rule' => QuantityRuleEnum::class,
            'discount_type' => DiscountTypeEnum::class,
            'quantity' => 'integer',
            'price_id' => 'integer',
            'discount_value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
