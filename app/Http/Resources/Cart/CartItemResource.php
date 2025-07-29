<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->itemable;
        return [
            'id' => $this->id,
            'type' => $this->when($item ,class_basename($item)),
            'item' => $this->when($item,function () use($item){
               return $item instanceof Design
                    ? new DesignResource($item)
                    : new TemplateResource($item);
            }),
            'specs' => CartItemSpecsResource::collection($this->whenLoaded('specs')),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'price' => $this->sub_total,
            'quantity' => $this->quantity,
        ];
    }
}
