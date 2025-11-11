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
            'comment_image' => $this->whenLoaded('media', function () {
                $image = $this->media
                    ->where('collection_name', 'review_reply')
                    ->first();
                return MediaResource::make($image);
            }),
            'created_at'=> $this->created_at?->toDateString(),
            'images' => $this->whenLoaded('media', function () {
                $images = $this->media
                    ->where('collection_name', 'reviews')
                    ->values();
                return MediaResource::collection($images);
            })
        ];
    }
}
