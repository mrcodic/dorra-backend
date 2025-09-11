<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use App\Models\Design;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        $cartable = $this->cartable;
        return [
            'id' => $this->id,
            'type' => $this->when($item, class_basename($item)),
            'item' => $this->when($item, function () use ($item) {
                return $item instanceof Design
                    ? new DesignResource($item)
                    : new TemplateResource($item);
            }),
            'specs' => CartItemSpecsResource::collection($this->whenLoaded('specs')),
            'product' => $this->when($cartable, function () use ($cartable) {
                return $cartable instanceof Product
                    ? new ProductResource($cartable)
                    : new CategoryResource($cartable);
            }),
            'price' => $this->sub_total,
            'quantity' => $this->quantity,
        ];
    }
}
