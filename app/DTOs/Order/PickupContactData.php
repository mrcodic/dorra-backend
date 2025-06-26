<?php

namespace App\DTOs\Order;

class PickupContactData
{
    public static function fromRequest($request): ?array
    {
        return [
            'first_name' => $request->pickup_contact_first_name,
            'last_name' => $request->pickup_contact_last_name,
            'phone' => $request->pickup_contact_phone_number,
            'email' => $request->pickup_contact_email,
        ];
    }
}


