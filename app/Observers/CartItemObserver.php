<?php

namespace App\Observers;

use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function created(CartItem $cartItem): void
    {
        //
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updated(CartItem $cartItem): void
    {
        if ($cartItem->wasChanged('sub_total'))
        {
            $cart = $cartItem->cart;
            $total = $cart->cartItems()->sum('sub_total');

            $cart->update([
                'price' => $total
            ]);

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
