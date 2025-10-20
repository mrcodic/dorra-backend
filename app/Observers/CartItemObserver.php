<?php

namespace App\Observers;

use App\Models\CartItem;

class CartItemObserver
{

    public function created(CartItem $cartItem): void
    {
        $this->updateCartTotals($cartItem);
    }


    public function updated(CartItem $cartItem): void
    {
        $this->refreshCartExpiration($cartItem);
        $this->updateCartItemSubtotal($cartItem);
        $this->updateCartTotals($cartItem);
    }


    public function deleted(CartItem $cartItem): void
    {
        $this->refreshCartExpiration($cartItem);
        $this->updateCartTotals($cartItem);
    }


    private function refreshCartExpiration(CartItem $cartItem): void
    {
        $cart = $cartItem->cart;

        $cart->expires_at = match (true) {
            $cart->user_id => now()->addHours(config('cart.user_expiration_hours', 24)),
            $cart->guest_id => now()->addMinutes(config('cart.guest_expiration_minutes', 60)),
            default => now()->addHour(),
        };

        $cart->saveQuietly();
    }


    private function updateCartTotals(CartItem $cartItem): void
    {
        $cart = $cartItem->cart;
        $cart->price = $cart->items()->sum('sub_total');
        $cart->saveQuietly();
    }

    private function updateCartItemSubtotal(CartItem $cartItem): void
    {
        $specsPrice = $cartItem->specs->sum(
            fn($item) => $item->productSpecificationOption?->price ?? 0
        );

        if ($cartItem->cartable?->has_custom_prices) {
            $subTotal = ($cartItem->productPrice?->price ?? $cartItem->product_price)
                + ($specsPrice ?: $cartItem->specs_price);
        } else {
            $subTotal = (
                    ($cartItem->cartable->base_price ?? $cartItem->product_price)
                    + ($specsPrice ?: $cartItem->specs_price)
                ) * $cartItem->quantity;
        }

        $cartItem->sub_total = $subTotal;
        $cartItem->saveQuietly();
    }
}
