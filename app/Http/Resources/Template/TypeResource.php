<?php

namespace App\Http\Resources\Template;

use App\Http\Resources\CountryCodeResource;
use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeResource extends JsonResource
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
            'value' => $this->value->value,
            'label' => $this->value->label(),
        ];
    }
}
