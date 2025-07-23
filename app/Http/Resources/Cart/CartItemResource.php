<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        return [
            'id' => $this->id,
            'type' => class_basename($item),
            'item' => $item instanceof \App\Models\Design
                ? new DesignResource($item)
                : new TemplateResource($item),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'price' => $this->sub_total,
        ];
    }
}
