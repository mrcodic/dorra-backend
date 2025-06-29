<?php

namespace App\DTOs\Order;

class OrderItemData
{
    public static function fromCartItems($cartItems): array
    {
        return $cartItems->map(function ($item) {
            return [

                    'design_id' => $item->id,
                    'quantity' => $item->quantity,
                    'base_price' => $item->product?->base_price,
                    'custom_product_price' => $item->productPrice?->price,
                    'total_price' => $item->total_price,
                ];
        })->toArray();
    }

}

