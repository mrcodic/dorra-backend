<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'body' => $this->body,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'created_since' => $this->created_at->diffForHumans(),
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
