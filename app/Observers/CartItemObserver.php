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
        $cart = $cartItem->cart;

        if ($cartItem->wasChanged('sub_total')) {
            $total = $cart->items()->sum('sub_total');

            $cart->update([
                'price' => $total
            ]);

        }
        if ($cartItem->wasChanged('quantity')) {
            if ($cartItem->product->has_custom_price) {

                $subTotal = $cartItem->productPrice + $cart->specs_price;
                $cartItem->sub_total = $subTotal;
                $cartItem->saveQuietly();
            } else {
                $subTotal = ($cartItem->productPrice * $cartItem->quantity) + $cart->specs_price;
                $cartItem->sub_total = $subTotal;
                $cartItem->saveQuietly();
            }

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
