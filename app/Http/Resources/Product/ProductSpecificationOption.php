<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSpecificationOption extends JsonResource
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
            'value' => $this->getTranslation('value', app()->getLocale()),
            'variant_key' => $this->getTranslation('value', 'en'),
            'price' => $this->price,
            'image' => $this->getFirstMediaUrl('productSpecificationOptions') ?: $this->getFirstMediaUrl('categorySpecificationOptions'),
        ];
    }
}
