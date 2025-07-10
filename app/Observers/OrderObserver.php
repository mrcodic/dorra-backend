<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;

class OrderObserver
{
    public function creating(Order $order)
    {
        $now = now();
        $dateString = $now->format('d-m-Y');
        $count = Order::whereDate('created_at', $now->toDateString())->count() + 1;
        $increment = str_pad($count, 3, '0', STR_PAD_LEFT);
        $order->order_number = "#ORD-{$dateString}-{$increment}";
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
         if (
            $order->isDirty('status') && 
            $order->status === StatusEnum::CONFIRMED &&
            !$order->invoice
        ) {
            CreateInvoiceJob::dispatch($order->load('orderItems'));
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
