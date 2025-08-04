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
            'title' => $this->when(isset($this->title), $this->title),
            'subtitle' => $this->when(isset($this->subtitle), $this->subtitle),
            'site_image' =>  $this->getFirstMediaUrl("carousels"),
            'mobile_image' =>  $this->getFirstMediaUrl("mobile_carousels"),
            'product' => ProductResource::make($this->whenLoaded('product')),

        ];
    }
}
