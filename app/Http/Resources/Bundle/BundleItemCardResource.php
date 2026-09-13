<?php

namespace App\Http\Resources\Bundle;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        $isProduct = $item instanceof Product;

        return [
            'bundle_item_id' => $this->id,
            'role' => $this->role?->value ?? $this->role,

            'item_id' => $item?->id,
            'item_name' => $item?->name,
            'item_type' => $isProduct ? 'product' : 'category',

            'quantity' => $this->quantity,

            'has_specs' => (bool) ($item?->has_specs ?? false),
            'require_customize_design' => (bool) ($item?->require_customize_design ?? false),
        ];
    }
}
