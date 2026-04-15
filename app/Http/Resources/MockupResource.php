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
        $types = $this->whenLoaded('types', fn() => $this->types, collect());

        $images = $types->mapWithKeys(function ($type) {
            $sideName = strtolower($type->value->name);

            $baseMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                return $media->getCustomProperty('side') === $sideName &&
                    $media->getCustomProperty('role') === 'base';
            });

            $maskMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                return $media->getCustomProperty('side') === $sideName &&
                    $media->getCustomProperty('role') === 'mask';
            });

            $shadowMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                return $media->getCustomProperty('side') === $sideName &&
                    $media->getCustomProperty('role') === 'shadow';
            });

            $displacementMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                return $media->getCustomProperty('side') === $sideName &&
                    $media->getCustomProperty('role') === 'displacement';
            });

            $lightMedia = $this->getMedia('mockups')->first(function ($media) use ($sideName) {
                return $media->getCustomProperty('side') === $sideName &&
                    $media->getCustomProperty('role') === 'light';
            });

            return [
                $type->value->value => [
                    'base_url' => optional($baseMedia)->getFullUrl(),
                    'mask_url' => optional($maskMedia)->getFullUrl(),
                    'shadow_url' => optional($shadowMedia)->getFullUrl(),
                    'displacement_url' => optional($displacementMedia)->getFullUrl(),
                    'light_url' => optional($lightMedia)->getFullUrl(),
                ],
            ];
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'types' => TypeResource::collection($this->whenLoaded('types')),
            'product' => CategoryResource::make($this->whenLoaded('category')),
            'colors' => $this->templateColors ?: $this->colors,
//            'colors' => $this->templateColors(request('template_id')) ?: $this->colors,
            'base_image_url' => $this->base_image_url,
            'area_top' => $this->area_top,
            'area_left' => $this->area_left,
            'area_width' => $this->area_width,
            'area_height' => $this->area_height,
            'side_settings' => MockupSideSettingResource::collection($this->whenLoaded('sideSettings')),
            'mockup_template_urls' => $this->getMedia('generated_mockups')
                ->map(fn($m) => $m->getFullUrl()),
            'images' => $images,
        ];
    }
}
