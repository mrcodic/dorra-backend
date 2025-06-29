<?php

namespace App\Http\Resources;

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

        return [
            'id' => $this->id,
            'number' => $this->order_number,
            'current_status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'statuses' => $statuses,
            'shipping_address' => $this->orderAddress,
            'items' => $this->orderItems,
            'sub_total' => $this->designs->pluck('total_price')->sum(),
            'total' => $this->price,
            'tax' => [
                'ratio' => setting('tax') * 100 . "%",
                'value' => getPriceAfterTax(setting('tax'), $this->orderItems->pluck('total_price')->sum()),
            ],
            'delivery' => setting('delivery'),
            'discount' => [
                'ratio' => 0 . "%",
                'value' => 0,
            ],
        ];
    }

}
