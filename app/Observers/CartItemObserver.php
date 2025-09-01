<?php

namespace App\Observers;

use App\Models\CartItem;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        $cart = $cartItem->cart;
        $total = $cart->items()->sum('sub_total');
        $cart->update([
            'price' => $total
        ]);

    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        $specsPrice = $cartItem->specs->sum(
            fn($item) => $item->productSpecificationOption?->price ?? 0
        );

        if ($cartItem->product->has_custom_prices) {
            $subTotal = ($cartItem->productPrice?->price ?? $cartItem->product_price)
                + ($specsPrice ?: $cartItem->specs_price);
        } else {
            $subTotal = (
                    ($cartItem->product->base_price ?? $cartItem->product_price)
                    + ($specsPrice ?: $cartItem->specs_price)
                ) * $cartItem->quantity;
        }

        $cartItem->sub_total = $subTotal;
        $cartItem->saveQuietly();

        $cart = $cartItem->cart;

        if ($cartItem->wasChanged('sub_total')) {
            $cart->price = $cart->items()->sum('sub_total');
            $cart->saveQuietly();
        }
    }


    /**
     * Handle the CartItem "deleted" event.
     */
    public function deleted(CartItem $cartItem): void
    {
        //
    }

    /**
     * Handle the CartItem "restored" event.
     */
    public function restored(CartItem $cartItem): void
    {
        //
    }

    /**
     * Handle the CartItem "force deleted" event.
     */
    public function forceDeleted(CartItem $cartItem): void
    {
        //
    }
}
