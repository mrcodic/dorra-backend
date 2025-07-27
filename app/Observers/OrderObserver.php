<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;

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
         if ($order->wasChanged('status') && $order->status == StatusEnum::CONFIRMED) {
             Invoice::updateOrCreate([
                 'order_id' => $order->id,
             ], [
                 'invoice_number' => $order->order_number,
                 'subtotal' => $order->subtotal,
                 'discount_amount' => $order->discount_amount,
                 'delivery_amount' => $order->delivery_amount,
                 'tax_amount' => $order->tax_amount,
                 'total_price' => $order->total_price,
                 'status' => $order->status,
                 'issued_date' => now(),
             ]);

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
