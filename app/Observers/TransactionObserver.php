<?php

namespace App\Observers;

use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;
use App\Services\CartService;

class TransactionObserver
{
    public function updated(Transaction $transaction,CartService $cartService): void
    {
        if ($transaction->payment_status == StatusEnum::PAID) {

            $order = $transaction->order;
            if ($order) {
                $order->update([
                    'status' => \App\Enums\Order\StatusEnum::CONFIRMED,
                    'payment_status' => StatusEnum::PAID,
                ]);
            }
            $cart = $cartService->getCurrentUserOrGuestCart();
            $cart?->items()->delete();
            if ($cart && $cart->discountCode) {
                $cart->discountCode->increment('used');
            }
            $cart?->update(['price' => 0, 'discount_amount' => 0, 'discount_code_id' => null]);
        }
        if ($transaction->payment_status == StatusEnum::UNPAID) {

            $transaction->order()->update([
                'payment_status' => StatusEnum::UNPAID,
            ]);
    }
}}
