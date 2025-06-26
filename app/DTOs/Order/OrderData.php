<?php

namespace App\DTOs\Order;

use App\Enums\Order\StatusEnum;
use Illuminate\Support\Facades\Auth;

class OrderData
{
    public static function fromCart($subTotal, $discountCode): array
    {
        return [
            'user_id' => Auth::guard('sanctum')->id(),
            'sub_total' => $subTotal,
            'discount_amount' => getDiscountAmount($discountCode->value ?? 0, $subTotal),
            'delivery_amount' => setting('delivery') ?? 30,
            'tax_amount' => setting('tax'),
            'total_price' => getTotalPrice($discountCode->value ?? 0, $subTotal),
            'status' => StatusEnum::PLACED,
        ];
    }
}
