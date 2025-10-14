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
        $lastOffer = $this->cartable->lastOffer;
        $sub  = (float) $this->getAttribute('sub_total');
        $val  = (float) ($lastOffer?->getRawOriginal('value') ?? 0);
        $after= $lastOffer ? round($sub * (1 - ($val / 100)), 2) : null;
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
                    ? new ProductResource($cartable->load('lastOffer'))
                    : new CategoryResource($cartable->load('lastOffer'));
            }),
            'price' => $this->sub_total,
            'product_price' => $this->product_price ?? ,
            'price_after_offer' => is_null($after) ? null : sprintf('%.2f', round($after, 2)),
            'quantity' => $this->quantity,
        ];
    }
}
