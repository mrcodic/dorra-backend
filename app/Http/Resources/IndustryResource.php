<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class IndustryResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $direct = (int) ($this->templates_count ?? 0);


        $childrenSum = (int) (
        $this->relationLoaded('children')
            ? $this->children->sum('templates_count')
            : $this->children()->withCount('templates')->get()->sum('templates_count')
        );

        $effectiveCount = $direct > 0 || !$this->relationLoaded('children') ? $direct : $childrenSum;

        return [
            'id'               => $this->id,
            'name'             => $this->getTranslation('name', app()->getLocale()),
            'sub-industries'   => IndustryResource::collection($this->whenLoaded('children')),
            'templates_count'  => $effectiveCount,

        ];
    }
}

