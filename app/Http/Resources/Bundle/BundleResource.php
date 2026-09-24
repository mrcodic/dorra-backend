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
            'slug' => $this->slug,
            'description' => $this->description,

            'status' => $this->status?->value ?? $this->status,
//            'repeat_type' => $this->repeat_type?->value ?? $this->repeat_type,
            'image_url' => $this->image_url,
            'display_bundle_on_visit' => (bool) $this->display_bundle_on_visit,
            'template_id' => $this->template_id,
            'is_attached_to_template' => ! empty($this->template_id),
            'attached_template' => $this->whenLoaded('template', function () {
                return [
                    'id' => $this->template?->id,
                    'name' => $this->template?->name,
                    'image_url' => $this->template?->getFirstMediaUrl('templates-preview')
                        ?: $this->template?->getFirstMediaUrl('templates'),
                ];
            }),
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
