<?php

namespace App\Observers;

use App\Enums\Payment\StatusEnum;
use App\Enums\Order\StatusEnum as OrderStatusEnum;
use App\Models\Transaction;

class TransactionObserver
{
    public function updated(Transaction $transaction): void
    {
        if (!$transaction->wasChanged('payment_status')) {
            return;
        }

        $order = $transaction->payable;

        if (!$order) {
            return;
        }

        match ($transaction->payment_status) {
            StatusEnum::PAID => $order->update([
                'payment_status' => StatusEnum::PAID,
                'status'         => OrderStatusEnum::CONFIRMED,
            ]),

            StatusEnum::CANCELLED => $order->update([
                'payment_status' => StatusEnum::CANCELLED,
                'status'         => OrderStatusEnum::CANCELLED,
            ]),

            StatusEnum::REFUNDED => $order->update([
                'payment_status' => StatusEnum::REFUNDED,
            ]),

            StatusEnum::FAILED => $order->update([
                'payment_status' => StatusEnum::FAILED,
            ]),

            StatusEnum::UNPAID => $order->update([
                'payment_status' => StatusEnum::UNPAID,
            ]),

            default => null,
        };
    }
}
