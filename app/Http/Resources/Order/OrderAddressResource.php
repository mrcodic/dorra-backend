<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\StateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->address_label,
            'line' => $this->address_line,
            'state'=> $this->state,
            'country'=> $this->country,
        ];
    }
}
