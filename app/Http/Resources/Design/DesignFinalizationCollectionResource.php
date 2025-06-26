<?php

namespace App\Http\Resources\Design;

use Illuminate\Http\Resources\Json\JsonResource;

class DesignFinalizationCollectionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'items' => DesignFinalizationResource::collection($this['specs']),
            'product' => $this['design']->product->name,
            'quantity' => $this['design']->quantity,
            'total_price' => $this['design']->total_price,
        ];
    }
}
