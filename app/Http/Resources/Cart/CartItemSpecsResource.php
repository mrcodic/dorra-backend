<?php

namespace App\Http\Resources\Cart;

use App\Http\Resources\Design\DesignResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Template\TemplateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemSpecsResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'item' => $this->spec_name,
            'selection' => $this->option_name,
            'price' => $this->option_price,

        ];
    }
}
