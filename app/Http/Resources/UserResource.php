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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'password_last_updated_at' => $this->password_updated_at?->format('j/n/Y'),
            'is_mobile_notifications_enabled' => $this->is_mobile_notifications_enabled,
            'is_email_notifications_enabled' => $this->is_email_notifications_enabled,
            'last_login_ip' => $this->last_login_ip,
            'last_login_at' => $this->last_login_at?->format('j M Y'),
            'joined_at' => $this->created_at->format('j M Y'),
            'image' => MediaResource::make($this->image),
            'country_details' => CountryCodeResource::make($this->whenLoaded('countryCode')),
            'connected_accounts' => SocialAccountResource::collection($this->whenLoaded('socialAccounts')),
            'notification_types' => NotificationTypeResource::collection($this->whenLoaded('notificationTypes')),
            'token' => $this->when($this->token, $this->token),
        ];
    }
}
