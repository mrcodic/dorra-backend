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
            if ($transaction->payment_status == StatusEnum::CANCELLED) {
                $transaction->order()->update([
                    'payment_status' => StatusEnum::CANCELLED,
                ]);
            }
            if ($transaction->payment_status == StatusEnum::REFUNDED) {
                $transaction->order()->update([
                    'payment_status' => StatusEnum::REFUNDED,
                ]);
                }
                if ($transaction->payment_status == StatusEnum::FAILED) {
                    $transaction->order()->update([
                        'payment_status' => StatusEnum::FAILED,
                    ]);
                }
            }
        }
    }
