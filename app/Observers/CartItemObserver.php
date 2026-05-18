<?php

namespace App\Observers;

use App\Models\CartItem;

class CartItemObserver
{
    public function created(CartItem $cartItem): void
    {
        $this->updateCartItemSubtotal($cartItem);
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
            (bool) $cart->user_id  => now()->addHours((int) config('cart.user_expiration_hours', 24)),
            (bool) $cart->guest_id => now()->addMinutes((int) config('cart.guest_expiration_minutes', 60)),
            default                => now()->addHour(),
        };

        $cart->saveQuietly();
    }

    private function updateCartTotals(CartItem $cartItem): void
    {
        $cart = $cartItem->cart;
        $cart->delivery_amount = 0;

        // SUM returns null when no rows exist — fall back to 0
        $cart->price = (float) ($cart->items()->selectRaw('SUM(sub_total) - SUM(discount_amount) as net')->value('net') ?? 0);

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

        $cartItem->sub_total       = $subTotal;
        $cartItem->discount_amount = min($cartItem->discount_amount ?? 0, $subTotal);
        $cartItem->saveQuietly();
    }
}
