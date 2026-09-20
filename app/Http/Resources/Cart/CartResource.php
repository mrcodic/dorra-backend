<?php

namespace App\Http\Resources\Cart;

use App\Enums\DiscountCode\TypeEnum;
use App\Enums\Item\TypeEnum as ItemTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items ?? collect();

        $normalItems = $this->standaloneCartItems();
        $bundleGroups = $this->resolveBundleGroups();

        $isDownload = $items->isNotEmpty() && $items->every(
                fn ($item) => $item->type == ItemTypeEnum::DOWNLOAD
            );


        $singleItemsSubTotal = $this->calculateItemsSubTotal($normalItems);

        $bundleGroupsTotal = round(
            $bundleGroups->sum('total'),
            2
        );

        $subAfter = round(
            $singleItemsSubTotal + $bundleGroupsTotal,
            2
        );

        $cartLevelDiscountCode = $this->discountCode?->scope === \App\Enums\DiscountCode\ScopeEnum::GENERAL
            ? $this->discountCode
            : null;

        return [
            'id' => $this->id,

            'items' => CartItemResource::collection($normalItems),

            'single_items_sub_total' => $singleItemsSubTotal,

            'has_bundle_items' => $bundleGroups->isNotEmpty(),
            'bundle_groups' => $bundleGroups,

            'bundle_groups_total' => $bundleGroupsTotal,

            'all_items_are_download' => $isDownload,

            'sub_total' => $subAfter,

            'total' => round(
                getTotalPrice($cartLevelDiscountCode, $subAfter, $this->delivery_amount, $isDownload),
                2
            ),

            'tax' => [
                'ratio' => ! $isDownload ? setting('tax') * 100 . '%' : '0%',
                'value' => ! $isDownload ? round(getPriceAfterTax(setting('tax'), $subAfter), 2) : 0,
            ],

            'delivery' => $this->delivery_amount,

            'discount' => [
                'id' => $this->discountCode?->id,
                'code' => $this->discountCode?->code,
                'ratio' => $subAfter > 0
                    ? (
                        ($this->discountCode?->type === TypeEnum::PERCENTAGE
                            ? (
                            intval($this->discountCode?->value * 100) == $this->discountCode?->value * 100
                                ? intval($this->discountCode?->value * 100)
                                : number_format($this->discountCode?->value * 100, 2, '.', '')
                            )
                            : (
                            intval(($this->discountCode?->value / $subAfter) * 100) == ($this->discountCode?->value / $subAfter) * 100
                                ? intval(($this->discountCode?->value / $subAfter) * 100)
                                : number_format(($this->discountCode?->value / $subAfter) * 100, 2, '.', '')
                            )
                        ) . '%'
                    )
                    : '0%',
                'value' => getDiscountAmount($this->discountCode, $subAfter) ?? 0,
            ],
        ];
    }

    private function standaloneCartItems()
    {
        return ($this->items ?? collect())
            ->filter(fn ($item) => empty($item->bundle_group_key))
            ->values();
    }

    private function calculateItemsSubTotal($items): float
    {
        return round(
            $items->sum(function ($item) use ($items) {
                $cartable = $item->cartable;

                $cartHasDiscount = $this->discount_amount > 0
                    || $items->contains(fn ($i) => $i->discount_amount > 0);

                $lastOffer = $cartHasDiscount ? null : $cartable?->lastOffer;

                $sub = (float) $item->sub_total;
                $val = (float) ($lastOffer?->getRawOriginal('value') ?? 0);

                if ($item->discount_amount > 0) {
                    return max(0, $sub - (float) $item->discount_amount);
                }

                if ($lastOffer) {
                    return round($sub * (1 - ($val / 100)), 2);
                }

                return $sub;
            }),
            2
        );
    }

    private function resolveBundleGroups()
    {
        return ($this->items ?? collect())
            ->filter(fn ($item) => ! empty($item->bundle_group_key))
            ->groupBy('bundle_group_key')
            ->map(function ($items, $groupKey) {
                $firstItem = $items->first();
                $bundle = $firstItem?->bundle;

                $subTotal = round((float) $items->sum('sub_total'), 2);
                $discountAmount = round((float) $items->sum('discount_amount'), 2);
                $total = round(max(0, $subTotal - $discountAmount), 2);

                return [
                    'bundle_group_key' => $groupKey,

                    'bundle_id' => $firstItem?->bundle_id,
                    'bundle_name' => $bundle?->name,

                    'items_count' => $items->count(),
                    'trigger_items_count' => $items->where('bundle_role', 'trigger')->count(),
                    'reward_items_count' => $items->where('bundle_role', 'reward')->count(),

                    'sub_total' => $subTotal,
                    'discount_amount' => $discountAmount,
                    'total' => $total,

                    'items' => CartItemResource::collection($items->values()),
                ];
            })
            ->values();
    }
}
