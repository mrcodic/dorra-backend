<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /* helper → turn  'base_grey.png'  into  https://yourapp.com/images/base_grey.png */
        $img = fn(string $file) => asset("images/{$file}");
        /* default values when Media-Library hasn’t been filled yet */
        $defaults = [
            'base_url'      => $img('basic_tshirt_front.png'),
            'shadow_url'    => $img('1678793789014.darkBlend1.png'),
            'mask_url'      => $img('mask.png'),
            'disp_url'      => $img('1678793789014.darkBlend1.png'),
            // match the PNG dimensions you exported (example 620 × 680 px)
            'pixel_w'       => 620,
            'pixel_h'       => 680,
            'offset_x'      => 0,
            'offset_y'      => 0,
            'default_color' => '#000000',
            'warp_mode'     => 'none',
        ];
        return [
            'id' => $this->when(isset($this->id), $this->id),
            'name' => $this->when(isset($this->name), $this->name),
            'type' => $this->when(isset($this->type), $this->type->label()),
            'colors' => $this->when(isset($this->colors), $this->colors),
            'area_top' => $this->when(isset($this->area_top), $this->area_top),
            'area_left' => $this->when(isset($this->area_left), $this->area_left),
            'area_width' => $this->when(isset($this->area_width), $this->area_width),
            'area_height' => $this->when(isset($this->area_height), $this->area_height),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'mockup_url' => $this->getFirstMediaUrl('mockups'),
            'base_url'      => $img('basic_tshirt_front.png'),
            'shadow_url'    => $img('1678793789014.darkBlend1.png'),
            'mask_url'      => $img('mask.png'),
            'disp_url'      => $img('1678793789014.darkBlend1.png'),
        ];
    }
}
