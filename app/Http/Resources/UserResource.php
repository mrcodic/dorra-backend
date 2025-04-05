<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'image' => $this->image,
            'country_details' => CountryCodeResource::make($this->whenLoaded('countryCode')),
            'connected_accounts' => SocialAccountResource::collection($this->whenLoaded('socialAccounts')),
            'token' => $this->when($this->token, $this->token),
        ];
    }
}
