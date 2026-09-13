<?php

namespace App\Http\Resources\Order;

use App\Models\Design;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $itemable = $this->itemable;
        $orderable = $this->orderable;

        $collectionName = $itemable
            ? Str::plural(Str::lower(class_basename($itemable)))
            : null;

        $subTotal = (float) ($this->sub_total ?? 0);
        $discountAmount = (float) ($this->discount_amount ?? 0);
        $finalPrice = max(0, $subTotal - $discountAmount);

        $isBundleItem = ! empty($this->bundle_group_key) || ! empty($this->bundle_id);

        return [
            'id' => $this->id,

            'product_name' => $orderable?->name ?? $itemable?->name,
            'quantity' => $this->quantity,
            'color' => $this->color,

            'total_price' => $subTotal,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,

            'is_bundle_item' => $isBundleItem,
            'bundle' => $isBundleItem ? [
                'id' => $this->bundle_id,
                'item_id' => $this->bundle_item_id,
                'group_key' => $this->bundle_group_key,
                'role' => $this->bundle_role,
                'name' => $this->bundle?->name,
            ] : null,

            'discount' => [
                'type' => $this->discountType($isBundleItem, $discountAmount),
                'id' => $isBundleItem ? null : $this->discountCode?->id,
                'code' => $isBundleItem ? null : $this->discountCode?->code,
                'value' => $discountAmount,
                'ratio' => $this->discountRatio($subTotal, $discountAmount),
            ],

            'mockup_design_image' => $this->mockupDesignImage(),

            'design_image' => $this->designImage($collectionName),

            'back_design_image' => $this->backDesignImage($collectionName),

            'specs' => OrderItemSpecResource::collection(
                $this->whenLoaded('specs')
            ),

            'item_type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
        ];
    }

    private function discountType(bool $isBundleItem, float $discountAmount): ?string
    {
        if ($isBundleItem) {
            return 'bundle';
        }

        if ($this->discountCode) {
            return 'discount_code';
        }

        if ($discountAmount > 0) {
            return 'offer';
        }

        return null;
    }

    private function mockupDesignImage(): ?string
    {
        $itemable = $this->itemable;

        if (! $itemable) {
            return null;
        }

        $orderItemPreview = $this->getFirstMediaUrl('order_item_mockups')
            ?: $this->getFirstMediaUrl('order_item_previews');

        $isDesign = $itemable instanceof Design;
        $isTemplate = $itemable instanceof Template;

        return match (true) {
            filled($orderItemPreview) && ! $isDesign => $orderItemPreview,

            $isDesign && (bool) ($itemable->linked_to_mockup ?? false) =>
            $itemable->getFirstMediaUrl('front-mockup-designs')
                ?: $itemable->getFirstMediaUrl('none-mockup-designs')
                ?: $itemable->getFirstMediaUrl('back-mockup-designs'),

            $isDesign && (bool) ($itemable->mockup_id ?? false) =>
            $itemable->getFirstMediaUrl('front-mockup-designs')
                ?: $itemable->getFirstMediaUrl('none-mockup-designs')
                ?: $itemable->getFirstMediaUrl('back-mockup-designs'),

            $isTemplate =>
            $itemable->getFirstMediaUrl('templates-preview')
                ?: $itemable->getFirstMediaUrl('templates'),

            default =>
            $itemable->getFirstMediaUrl(
                Str::plural(Str::lower(class_basename($itemable)))
            ),
        };
    }

    private function designImage(?string $collectionName): ?string
    {
        $itemable = $this->itemable;

        if (! $itemable || ! $collectionName) {
            return null;
        }

        return $itemable->approach === 'without_editor'
            ? $itemable->getFirstMediaUrl($collectionName . '-preview')
            : $itemable->getFirstMediaUrl($collectionName);
    }

    private function backDesignImage(?string $collectionName): ?string
    {
        $itemable = $this->itemable;

        if (! $itemable || ! $collectionName) {
            return null;
        }

        if ((bool) ($itemable->use_front_as_back ?? false)) {
            return $itemable->getFirstMediaUrl($collectionName . '-preview');
        }

        return $itemable->approach === 'without_editor'
            ? $itemable->getFirstMediaUrl('back-' . $collectionName . '-preview')
            : $itemable->getFirstMediaUrl('back_' . $collectionName);
    }

    private function discountRatio(float $subTotal, float $discountAmount): string
    {
        if ($subTotal <= 0 || $discountAmount <= 0) {
            return '0%';
        }

        $ratio = ($discountAmount / $subTotal) * 100;

        return rtrim(
                rtrim(number_format($ratio, 2, '.', ''), '0'),
                '.'
            ) . '%';
    }
}
