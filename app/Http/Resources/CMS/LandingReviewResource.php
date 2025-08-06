<?php

namespace App\Http\Resources\CMS;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingReviewResource extends JsonResource
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
            'customer'=> $this->customer,
            'rate'=> $this->rate,
            'date'=> $this->date,
            'review'=> $this->review,
            'image'=> $this->whenLoaded('media', function () {
                return $this->getFirstMediaUrl('reviews_landing_images');
            })

        ];
    }
}
