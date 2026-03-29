<?php

namespace App\Http\Resources;


use App\Http\Resources\Template\TypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockupSideSettingResource extends JsonResource
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
            'side' => $this->side,
            'is_active' => $this->is_active,
            'warp_points' => $this->warp_points,
            'render_presets' => $this->render_presets,
            'mockup' => MockupResource::make($this->whenLoaded('mockup')),
        ];
    }

}
