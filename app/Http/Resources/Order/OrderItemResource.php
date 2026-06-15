<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductSpecificationResource;
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
        $collectionName = Str::plural(Str::lower(class_basename($this->itemable)));
        return [
            'id' => $this->id,
           'product_name' => $this->orderable?->name,
           'quantity' => $this->quantity,
            'color' => $this->color,
            'total_price' => $this->sub_total,
            'discount_amount' => $this->discount_amount,
            'final_price' => max(0,$this->sub_total - $this->discount_amount),
            'mockup_design_image' => (function () {
                $orderItemPreview =
                    $this->getFirstMediaUrl('order_item_mockups')
                        ?: $this->getFirstMediaUrl('order_item_previews');

                $isDesign    = $this->itemable && $this->itemable instanceof \App\Models\Design;
                $isTemplate  = $this->itemable && $this->itemable instanceof \App\Models\Template;

                return match (true) {
                    filled($orderItemPreview) =>
                    $orderItemPreview,

                    $isDesign && $this->itemable->linked_to_mockup =>
                    $this->itemable->getFirstMediaUrl('front-mockup-designs')
                        ?: $this->itemable->getFirstMediaUrl('none-mockup-designs')
                        ?: $this->itemable->getFirstMediaUrl('back-mockup-designs'),

                    $isTemplate =>
                    $this->itemable?->getFirstMediaUrl('templates-preview')
                        ?: $this->itemable?->getFirstMediaUrl('templates'),

                    default =>
                    $this->itemable?->getFirstMediaUrl(
                        Str::plural(Str::lower(class_basename($this->itemable)))
                    ),
                };
            })(),
            'design_image' =>($this->approach == 'without_editor'
                ? $this->itemable?->getFirstMediaUrl($collectionName.'-preview')
                : $this->itemable?->getFirstMediaUrl($collectionName)),
            'back_design_image' => $this->use_front_as_back
                ? $this->itemable?->getFirstMediaUrl($collectionName.'-preview')
                : ($this->itemable?->approach == 'without_editor'
                    ? $this->itemable?->getFirstMediaUrl('back-'.$collectionName.'-preview')
                    : $this->itemable?->getFirstMediaUrl('back_'.$collectionName)),
            'specs' => OrderItemSpecResource::collection($this->whenLoaded('specs')),
            'item_type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],
        ];
    }
}
