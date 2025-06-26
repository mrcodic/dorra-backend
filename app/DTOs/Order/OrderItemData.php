<?php

namespace App\DTOs\Order;

class OrderItemData
{
    public static function fromCartItems($cartItems): array
    {
        return $cartItems->map(function ($item) {
            return [
                'design_id' => $item->design_id,
                'quantity' => $item->design->quantity,
                'base_price' => $item->design->base_price,
                'custom_product_price' => $item->design->productPrice->price,
                'total_price' => $item->design->total_price,

            ];
        })->toArray();
    }
}

