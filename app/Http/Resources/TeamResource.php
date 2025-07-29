<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'created' => $this->created_at->format('d/m/Y'),
            'last_edit' => $this->updated_at->format('d/m/Y'),
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'ownered_by_me' => $this->owner?->id == auth('sanctum')->user()?->id,
            'designs_count' => $this->designs_count,

        ];
    }
}
