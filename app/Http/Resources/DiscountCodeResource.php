<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
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
            'code' => $this->code,

            'type' => [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ],

            'value' => $this->value,
            'original_value' => $this->getRawOriginal('value'),

            'max_usage' => $this->max_usage,
            'used' => $this->used,
            'remaining_usage' => is_null($this->max_usage) ? null : max(0, $this->max_usage - $this->used),

            'expired_at' => $this->expired_at,
            'is_expired' => $this->expired_at ? $this->expired_at <= now() : false,

            'scope' => [
                'value' => $this->scope?->value,
                'label' => method_exists($this->scope, 'label') ? $this->scope?->label() : null,
            ],

            'code_mode' => $this->code_mode,
            'show_for_new_registered_users' => (bool) $this->show_for_new_registered_users,

            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                    ];
                });
            }),

            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
