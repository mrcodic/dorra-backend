<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductSpecificationResource;
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
            'width' => $this->when(isset($this->width_pixel), $this->width_pixel),
            'height' => $this->when(isset($this->height_pixel), $this->height_pixel),
            'original_width' => $this->when(isset($this->width), $this->width),
            'original_height' => $this->when(isset($this->height), $this->height),
            'unit' => $this->when(isset($this->unit), [
                'value' => $this->unit?->value,
                'label' => $this->unit?->label(),
            ]),
            'dpi' => $this->when(isset($this->dpi), $this->dpi ?? 300),
            'status' => $this->when(isset($this->status), [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
            ]),
            'type' => $this->when(isset($this->type), [
                'value' => $this->type?->value,
                'label' => $this->type?->label(),
            ]),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'specs' => ProductSpecificationResource::collection($this->whenLoaded('specifications')),
            'source_design_svg' => $this->when(isset($this->image), $this->image),
            'base64_preview_image' => $this->when(isset($this->image), $this->image),
            'product_type' => $this->product?->getTranslation('name','en') == 'T-shirt' ? 'T-shirt' : 'other' ,
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
