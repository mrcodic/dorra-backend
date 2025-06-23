<?php

namespace App\Http\Resources\Design;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\TemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignFinalizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "product"=>$this->pivot->design->product->name,
            "item" => $this->pivot->productSpecification->name,
            "selection" => $this->pivot->specOption->value,
            "price" => $this->pivot->specOption->price,
            "quantity"=> $this->pivot->design->quantity,
            "total_price"=>$this->pivot->design->total_price,
        ];
    }
}
