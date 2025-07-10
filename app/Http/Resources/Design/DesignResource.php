<?php

namespace App\Http\Resources\Design;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\TemplateResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
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
            'name'=> $this->name,
            'description'=> $this->description,
            'design_data' => $this->when(request('design_data') == true, $this->design_data),
            'design_image' => $this->getFirstMediaUrl('designs'),
            'current_version' => $this->current_version,
            'product' => ProductResource::make($this->whenLoaded('product') ?? $this->whenLoaded('directProduct')),
            'owner' => UserResource::make($this->whenLoaded('owner')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'template' => TemplateResource::make($this->whenLoaded('template')),
            'placed_on' => optional($this->pivot)->created_at?->format('d/m/Y'),
            'price' => $this->total_price,
            'quantity' => $this->productPrice?->quantity ?? $this->quantity,
            'ownered_by_me' => $this->owner?->id == auth('sanctum')->user()?->id,
            'type' => 'design',
            'delete_since' => $this->deleted_at?->diffForHumans(),
            'is_saved' => $this->when(!$this->deleted_at,fn() => $this->saves->contains(fn($save) => $save->user_id === auth('sanctum')->id()),)


        ];
    }
}
