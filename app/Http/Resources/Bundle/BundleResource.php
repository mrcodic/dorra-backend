<?php

namespace App\Http\Resources\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'description' => $this->description,

            'status' => $this->status?->value ?? $this->status,
//            'repeat_type' => $this->repeat_type?->value ?? $this->repeat_type,
            'image_url' => $this->image_url,
            'display_bundle_on_visit' => (bool) $this->display_bundle_on_visit,

            'start_at' => $this->start_at?->format('Y-m-d'),
            'end_at' => $this->end_at?->format('Y-m-d'),
            'saving' => $this->saving,
            'trigger' => $this->whenLoaded('trigger', function () {
                return BundleItemResource::make($this->trigger);
            }),

            'rewards' => $this->whenLoaded('rewards', function () {
                return BundleItemResource::collection($this->rewards);
            }),
        ];
    }
}
