<?php

namespace App\Observers;

use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;

class TransactionObserver
{
    public function updated(Transaction $transaction): void
    {
        if ($transaction->payment_status == StatusEnum::PAID) {

            $transaction->order()->update([
                'status' => \App\Enums\Order\StatusEnum::CONFIRMED,
            ]);
    }
}}
