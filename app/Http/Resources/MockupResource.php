<?php

namespace App\Http\Resources;


use App\Http\Resources\Template\TypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = collect($this->types)
            ->mapWithKeys(function ($type)  {
                $sideName = strtolower($type->value->name); // e.g., FRONT, BACK, NONE

                $baseMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                    return $media->getCustomProperty('side') === $sideName &&
                        $media->getCustomProperty('role') === 'base';
                });

                $maskMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                    return $media->getCustomProperty('side') === $sideName &&
                        $media->getCustomProperty('role') === 'mask';
                });

                return [
                    $sideName => [
                        'base_url' => $baseMedia?->getFullUrl() ,
                        'mask_url' => $maskMedia?->getFullUrl(),
                    ]
                ];
            });
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'types' => TypeResource::collection($this->whenLoaded('types')),
            'colors' => $this->when(isset($this->colors), $this->colors),
            'area_top' => $this->area_top,
            'area_left' => $this->area_left,
            'area_width' => $this->area_width,
            'area_height' => $this->area_height,
            'product' => CategoryResource::make($this->whenLoaded('category')),
            'mockup_template_urls' => $this->getMedia('generated_mockups')
                ->map(fn($m) => $m->getFullUrl()),
            'images' => $images,
        ];
    }
}
