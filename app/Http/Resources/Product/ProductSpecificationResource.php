<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSpecificationResource extends JsonResource
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
            'name' => $this->getTranslation('name', app()->getLocale()),
            'options' => ProductSpecificationOption::collection($this->whenLoaded('options')),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'option' => ProductSpecificationOption::make($this->whenLoaded('pivot.option'))
        ];
    }
}
