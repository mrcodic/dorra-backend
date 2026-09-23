<?php

namespace App\Http\Resources\Bundle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'description' => $this->description,

            'image_url' => $this->image_url,

            'status' => $this->status?->value ?? $this->status,

            'display_bundle_on_visit' => (bool) $this->display_bundle_on_visit,
            'template_id' => $this->template_id,
            'is_attached_to_template' => ! empty($this->template_id),

            'saving' => $this->saving,

            'items_count' => $this->getBundleItemsCount(),

            'items' => BundleItemCardResource::collection(
                collect([$this->whenLoaded('trigger')])
                    ->merge($this->whenLoaded('rewards'))
                    ->filter()
                    ->values()
            ),
        ];
    }

    private function getBundleItemsCount(): int
    {
        $count = 0;

        if ($this->relationLoaded('trigger') && $this->trigger) {
            $count++;
        }

        if ($this->relationLoaded('rewards')) {
            $count += $this->rewards->count();
        }

        return $count;
    }
}
