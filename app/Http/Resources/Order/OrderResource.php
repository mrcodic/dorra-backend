<?php

namespace App\Http\Resources\Order;

use App\Enums\Order\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            $statuses[strtolower($statusCase->label())] = $statusCase->value <= $this->status->value;
        }

        $paidRatio = $this->subtotal > 0
            ? ($this->discount_amount) / $this->subtotal
            : 0;

        return [
            'id' => $this->id,
            'number' => $this->order_number,
            'current_status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'statuses' => $statuses,
            'shipping_address' => OrderAddressResource::make($this->whenLoaded('orderAddress')),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'sub_total' => $this->subtotal,
            'total' => $this->total_price,
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->orderItems->pluck('total_price')->sum()),
            ],
            'delivery' => setting('delivery'),
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
