<?php

namespace App\DTOs\Order;

use App\Enums\Order\StatusEnum;
use App\Models\Guest;
use Illuminate\Support\Facades\Auth;

class OrderData
{
    public static function fromCart($subTotal, $discountCode): array
    {
        
        return [
            'user_id' => Auth::guard('sanctum')?->id(),
            'guest_id' => request()->hasCookie('cookie_id') ? Guest::whereCookieValue(request()->cookie('cookie_id'))->first()?->id : null,
            'payment_method_id' => request('payment_method_id'),
            'subtotal' => $subTotal,
            'discount_amount' => getDiscountAmount($discountCode ?? 0, $subTotal),
            'delivery_amount' => setting('delivery') ?? 30,
            'tax_amount' => setting('tax'),
            'total_price' => getTotalPrice($discountCode ?? 0, $subTotal),
            'status' => StatusEnum::PENDING,
        ];
    }
}
