<?php

namespace App\Http\Resources\Order;

use App\Enums\Order\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = [];
        $statusCases = StatusEnum::cases();

        foreach ($statusCases as $statusCase) {
            $statuses[strtolower($statusCase->label())] =
                $statusCase->value <= $this->status->value;
        }

        $paidRatio = $this->subtotal > 0
            ? ($this->discount_amount) / $this->subtotal
            : 0;

        $orderItems = $this->relationLoaded('orderItems')
            ? $this->orderItems
            : collect();

        $normalItems = $orderItems
            ->filter(fn ($orderItem) => empty($orderItem->bundle_group_key))
            ->values();

        $bundleGroups = $orderItems
            ->filter(fn ($orderItem) => ! empty($orderItem->bundle_group_key))
            ->groupBy('bundle_group_key')
            ->map(function ($items, $groupKey) {
                $firstItem = $items->first();

                $totalPrice = round((float) $items->sum('sub_total'), 2);
                $discountAmount = round((float) $items->sum('discount_amount'), 2);
                $finalPrice = round(max(0, $totalPrice - $discountAmount), 2);

                return [
                    'bundle_group_key' => $groupKey,

                    'bundle_id' => $firstItem?->bundle_id,
                    'bundle_name' => $firstItem?->bundle?->name,

                    'items_count' => $items->count(),

                    'trigger_items_count' => $items
                        ->where('bundle_role', 'trigger')
                        ->count(),

                    'reward_items_count' => $items
                        ->where('bundle_role', 'reward')
                        ->count(),

                    'total_price' => $totalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $finalPrice,

                    'items' => OrderItemResource::collection($items->values()),
                ];
            })
            ->values();

        return [
            'id' => $this->id,
            'number' => $this->order_number,

            'current_status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'icon' => $this->status->icon(),
            ],

            'statuses' => $statuses,

            'shipping_address' => OrderAddressResource::make(
                $this->whenLoaded('orderAddress')
            ),

            'items' => OrderItemResource::collection($normalItems),

            'has_bundle_items' => $bundleGroups->isNotEmpty(),
            'bundle_groups' => $bundleGroups,

            'items_count' => $normalItems->count() + $bundleGroups->count(),

            'items_images' => $orderItems->map(function ($orderItem) {
                $itemable = $orderItem->itemable;

                return $itemable
                    ? $itemable->getFirstMediaUrl(
                        Str::plural(Str::lower(class_basename($itemable)))
                    )
                    : null;
            })->values(),

            'items_names' => $orderItems->map(function ($orderItem) {
                return $orderItem->itemable?->name;
            })->values(),

            'sub_total' => $this->subtotal,
            'total' => $this->total_price,

            'tax' => [
                'ratio' => $this->tax_amount && $this->total_price
                    ? ($this->tax_amount / ($this->total_price - $this->tax_amount)) * 100
                    : 0,
                'value' => $this->tax_amount,
            ],

            'delivery' => $this->delivery_amount,

            'discount' => [
                'ratio' => round($paidRatio * 100) . '%',
                'value' => $this->discount_amount,
            ],

            'placed_on' => $this->created_at->format('d/m/Y'),

            'payment_method' => $this->paymentMethod?->name,
            'payment_status' => $this->payment_status,
        ];
    }
}
