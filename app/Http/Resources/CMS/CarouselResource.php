<?php

namespace App\Http\Resources\CMS;

use App\Http\Resources\CategoryResource;
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
            'title' => $this->when(
                $this->getTranslation('title', app()->getLocale()) !== null,
                $this->getTranslation('title', app()->getLocale())
            ),
            'subtitle' => $this->when(
                $this->getTranslation('subtitle', app()->getLocale()) !== null,
                $this->getTranslation('subtitle', app()->getLocale())
            ),
            'site_image' => $this->getFirstMediaUrl("carousels"),
            'mobile_image' => $this->getFirstMediaUrl("mobile_carousels"),
            'site_image_ar' => $this->getFirstMediaUrl("carousels_ar"),
            'mobile_image_ar' => $this->getFirstMediaUrl("mobile_carousels_ar"),
            'type' => $this->category_id ? 'category' : 'product',
            'product' =>$this->category_id
                ? CategoryResource::make($this->whenLoaded('category'))
                :ProductResource::make($this->whenLoaded('product')) ,
            'title_color'=>$this->title_color,
            'subtitle_color'=>$this->subtitle_color,
        ];

    }
}
