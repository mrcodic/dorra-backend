<?php

namespace App\Http\Resources;

use App\Http\Resources\Design\DesignResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
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
            'name' => $this->when(isset($this->name), $this->name),
            'description' => $this->when(isset($this->description), $this->description),
            'designs_count' => $this->when(isset($this->designs_count), $this->designs_count),
            'designs' => DesignResource::collection($this->whenLoaded('designs')),
            'delete_since' => $this->deleted_at?->diffForHumans(),
        ];
    }
}
