<?php

namespace App\Http\Resources\CMS;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarouselResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'title' => $this->when($this->getTranslation('title') !== null, $this->getTranslation('title')),
            'subtitle' => $this->when($this->getTranslation('subtitle') !== null, $this->getTranslation('subtitle')),
            'site_image' =>  $this->getFirstMediaUrl("carousels"),
            'mobile_image' =>  $this->getFirstMediaUrl("mobile_carousels"),
//            'mobile_image' =>  $this->getFirstMediaUrl("mobile_carousels"),
            'product' => ProductResource::make($this->whenLoaded('product')),

        ];
    }
}
