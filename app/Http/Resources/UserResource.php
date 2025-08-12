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
            'id' => $this->when(isset($this->id), $this->id),
            'first_name' => $this->when(isset($this->first_name), $this->first_name),
            'last_name' => $this->when(isset($this->last_name), $this->last_name),
            'name' => $this->when(isset($this->name), $this->name),
            'email' => $this->when(isset($this->email), $this->email),
            'phone_number' => $this->phone_number,
            'password_last_updated_at' => $this->when(isset($this->password_updated_at), optional($this->password_updated_at)->format('j/n/Y')),
            'is_mobile_notifications_enabled' => $this->when(isset($this->is_mobile_notifications_enabled), $this->is_mobile_notifications_enabled),
            'is_email_notifications_enabled' => $this->when(isset($this->is_email_notifications_enabled), $this->is_email_notifications_enabled),
            'last_login_ip' => $this->when(isset($this->last_login_ip), $this->last_login_ip),
            'last_login_at' => $this->when(isset($this->last_login_at), optional($this->last_login_at)->format('j M Y')),
            'joined_at' => $this->when(isset($this->created_at), optional($this->created_at)->format('j M Y')),
            'status' => $this->when(isset($this->status), $this->status),
            'image' => $this->image ? MediaResource::make($this->image) : null,
            'country_details' => CountryCodeResource::make($this->whenLoaded('countryCode')),
            'connected_accounts' => SocialAccountResource::collection($this->whenLoaded('socialAccounts')),
            'notification_types' => NotificationTypeResource::collection($this->whenLoaded('notificationTypes')),
            'dorra_auth_token' => $this->when(isset($this->token), $this->token),
        ];
    }

}
