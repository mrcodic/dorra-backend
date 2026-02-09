<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'recommended_for' => $this->recommended_for,
            'price' => $this->price,
            'credits' => $this->credits,
            'features' => FeatureResource::collection($this->whenLoaded('features')),
        ];
    }
}
