<?php

namespace App\Models;

use App\Enums\Bundle\RepeatTypeEnum;
use App\Enums\Bundle\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Bundle extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'description',
        'status',
        'application_type',
        'repeat_type',
        'display_bundle_on_visit',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusEnum::class,
            'repeat_type' => RepeatTypeEnum::class,
            'display_bundle_on_visit' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }
    protected $appends = [
        'saving',
    ];

    public function getSavingAttribute(): array
    {
        $items = collect();

        if ($this->relationLoaded('trigger') && $this->trigger) {
            $items->push($this->trigger);
        }

        if ($this->relationLoaded('rewards')) {
            $items = $items->merge($this->rewards);
        }

        if ($items->isEmpty()) {
            return $this->emptySavingSummary();
        }

        $originalTotal = 0.0;
        $discountTotal = 0.0;

        foreach ($items as $bundleItem) {
            $originalPrice = $this->getBundleItemOriginalPrice($bundleItem);

            $originalTotal += $originalPrice;

            if ($this->isRewardItem($bundleItem)) {
                $discountTotal += $this->getBundleItemDiscountAmount(
                    $bundleItem,
                    $originalPrice
                );
            }
        }

        $finalTotal = max($originalTotal - $discountTotal, 0);

        $savingPercentage = $originalTotal > 0
            ? round(($discountTotal / $originalTotal) * 100, 2)
            : 0;

        return [
            'original_total' => round($originalTotal, 2),
            'discount_total' => round($discountTotal, 2),
            'final_total' => round($finalTotal, 2),
            'saving_percentage' => $savingPercentage,
            'saving_percentage_label' => $savingPercentage . '%',
        ];
    }

    private function emptySavingSummary(): array
    {
        return [
            'original_total' => 0,
            'discount_total' => 0,
            'final_total' => 0,
            'saving_percentage' => 0,
            'saving_percentage_label' => '0%',
        ];
    }

    private function getBundleItemOriginalPrice(BundleItem $bundleItem): float
    {
        $item = $bundleItem->itemable;

        if (! $item) {
            return 0;
        }

        if ($bundleItem->price_id && method_exists($item, 'prices')) {
            $price = $item->prices()
                ->whereKey($bundleItem->price_id)
                ->first();

            if ($price) {
                return $this->getPriceAmount($price);
            }
        }

        $basePrice =
            data_get($item, 'base_price')
            ?? data_get($item, 'price')
            ?? 0;

        return (float) $basePrice * (int) $bundleItem->quantity;
    }

    private function getBundleItemDiscountAmount(
        BundleItem $bundleItem,
        float $originalPrice
    ): float {
        if ($originalPrice <= 0) {
            return 0;
        }

        $discountType = $bundleItem->discount_type?->value
            ?? $bundleItem->discount_type;

        if ($discountType === 'free') {
            $discountAmount = $originalPrice;
        } elseif ($discountType === 'percentage') {
            $discountAmount = $originalPrice * ((float) $bundleItem->discount_value / 100);
        } else {
            $discountAmount = 0;
        }

        if ($bundleItem->max_discount_amount !== null) {
            $discountAmount = min(
                $discountAmount,
                (float) $bundleItem->max_discount_amount
            );
        }

        return min($discountAmount, $originalPrice);
    }

    private function isRewardItem(BundleItem $bundleItem): bool
    {
        return ($bundleItem->role?->value ?? $bundleItem->role) === 'reward';
    }

    private function getPriceAmount($price): float
    {
        return (float) (
            data_get($price, 'price'));
    }
    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class)->orderBy('sort_order');
    }

    public function trigger(): HasOne
    {
        return $this->hasOne(BundleItem::class)
            ->where('role', 'trigger');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(BundleItem::class)
            ->where('role', 'reward')
            ->orderBy('sort_order');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', StatusEnum::ACTIVE->value)
            ->where(function (Builder $query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === StatusEnum::DRAFT) {
            return 'Draft';
        }

        if ($this->start_at?->isFuture()) {
            return 'Scheduled';
        }

        if ($this->end_at?->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }

    protected static function booted(): void
    {
        static::deleting(function (Bundle $bundle) {
            if (! $bundle->isForceDeleting()) {
                $bundle->items()->get()->each->delete();
            }
        });
    }
}
