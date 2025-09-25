<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\JobTicket;
use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;
use App\Models\OrderItem;
use App\Models\Station;

class OrderObserver
{
    public function creating(Order $order)
    {
        $now = now();
        $dateString = $now->format('d-m-Y');
        $order->order_number = "#ORD-{$dateString}-".mt_rand(100, 999);
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (request()->user() instanceof Admin)
        {
            $order->update(["status"=> StatusEnum::CONFIRMED]);
        }

    }
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === StatusEnum::CONFIRMED) {
            $order->loadMissing(['paymentMethod']);
            if ($order->paymentMethod?->code === 'cash_on_delivery') {
                $order->update([
                        'payment_status' => \App\Enums\Payment\StatusEnum::PAID
                    ]);
            }
            $order->orderItems->each(function (OrderItem $orderItem) use ($order) {
                $sequence = JobTicket::whereBelongsTo($orderItem)->count() + 1;
                JobTicket::create([
                    'code' => sprintf(
                        "JT-%s-%d-%02d",
                        now()->format('Ymd'),
                        $orderItem->id,
                        $sequence
                    ),
                    'order_item_id' => $orderItem->id,
                    'station_id'    => Station::query()->whereCode('prepress')->first()->id,
                    'priority'      => 1,
                    'due_at'        => now()->addDay(),
                    'status'        => 0,
                ]);
            });
            CreateInvoiceJob::dispatch($order);

        }
        if ($order->wasChanged('status') && $order->status === StatusEnum::PENDING) {
            $order->loadMissing(['paymentMethod']);
            if ($order->paymentMethod?->code === 'cash_on_delivery') {
                $order->update([
                        'payment_status' => \App\Enums\Payment\StatusEnum::PENDING
                    ]);
            }
            CreateInvoiceJob::dispatch($order);
        }

    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
