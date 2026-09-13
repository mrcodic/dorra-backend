<?php

namespace App\Http\Resources\Bundle;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bundle_item_id' => $this->id,
            'role' => $this->role?->value ?? $this->role,

            'item_id' => $this->itemable?->id,
            'item_name' => $this->itemable?->name,
            'item_type' => $this->itemable?->getMorphClass() == Product::class ? 'product' : 'category',

            'quantity' => $this->quantity,
        ];
    }
}
