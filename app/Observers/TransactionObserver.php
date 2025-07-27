<?php

namespace App\Observers;

use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;

class TransactionObserver
{
    public function updated(Transaction $transaction): void
    {
        if ($transaction->payment_status == StatusEnum::PAID) {

            $order = $transaction->order;
            if ($order) {
                $order->update([
                    'status' => \App\Enums\Order\StatusEnum::CONFIRMED,
                    'payment_status' => StatusEnum::PAID,
                ]);
            }

        }
        if ($transaction->payment_status == StatusEnum::UNPAID) {

            $transaction->order()->update([
                'payment_status' => StatusEnum::UNPAID,
            ]);
    }
}}
