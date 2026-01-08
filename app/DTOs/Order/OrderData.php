<?php

namespace App\DTOs\Order;

use App\Enums\Item\TypeEnum;
use App\Enums\Order\StatusEnum;
use App\Models\Guest;
use Illuminate\Support\Facades\Auth;

class OrderData
{
    public static function fromCart($subTotal, $discountCode,$cart): array
    {
        $allDownload = $cart->items->every(fn($item) => $item->type == TypeEnum::DOWNLOAD);

        return [
            'user_id' => Auth::guard('sanctum')?->id(),
            'guest_id' => request()->hasCookie('cookie_id') ? Guest::whereCookieValue(request()->cookie('cookie_id'))->first()?->id : null,
            'payment_method_id' => request('payment_method_id'),
            'subtotal' => $subTotal,
            'discount_amount' => getDiscountAmount($discountCode ?? 0, $subTotal),
            'offer_amount' => $cart->items->sum('offer_amount'),
            'delivery_amount' => $cart->delivery_amount ?? 0,
            'tax_amount' => !$allDownload ? getPriceAfterTax(setting('tax'), $subTotal) : 0,
            'total_price' =>round(getTotalPrice($discountCode ?? 0, $subTotal, $cart->delivery_amount),2),
            'status' => StatusEnum::PENDING,
        ];
    }
}
