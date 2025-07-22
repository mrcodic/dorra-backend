<?php

namespace App\Http\Resources\Template;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
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
            'name_en' =>$this->getTranslation('name','en'),
            'name_ar' =>$this->getTranslation('name','ar'),
            'description' => $this->description,
            'design_data' => $this->when(
                request()->boolean('with_design_data', true),
                $this->design_data
            ),
            'status' => $this->when(isset($this->status), [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ]),
            'types' => TypeResource::collection($this->whenLoaded('types')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'source_design_svg' => $this->when(isset($this->image), $this->image),
            'base64_preview_image' => $this->when(isset($this->image), $this->image),
            'has_mockup' => (boolean) $this->products->contains('has_mockup', true),
            'last_saved' => $this->when(isset($this->updated_at), $this->updated_at?->format('d/m/Y, g:i A')),
//            'add_to_cart' => $this->canBeAddedToCart(),

        ];
    }
//    protected function canBeAddedToCart(): bool
//    {
//        $user = auth('sanctum')->user();
//
//
//        if (!$user) {
//            return false;
//        }
//        // Get the design IDs associated with this template
//        $designIds = $user->designs?->pluck('id');
//        // Check if any of these designs exist in the user's cart
//        return !$user->cart?->cartItems()
//            ->whereIn('design_id', $designIds)
//            ->exists();
//    }

}
