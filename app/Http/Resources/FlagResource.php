<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlagResource extends JsonResource
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
            'name' => $this->getTranslation('name',app()->getLocale()),
            'templates' => TemplateResource::collection($this->whenLoaded('templates')),
            'products' => ProductResource::collection($this->whenLoaded('products')),

        ];
    }
}
