<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user' => UserResource::make($this->whenLoaded('user')),
            'rating'    => (int) $this->rating,
            'review'    => $this->review,
            'comment'   => $this->comment,
            'comment_at'=> $this->comment_at ? $this->comment_at->toDateString() : null,
            'created_at'=> $this->created_at?->toDateString(),
            'images' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
