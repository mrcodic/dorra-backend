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
        if (!property_exists($order, 'isReplication') || !$order->isReplication) {
            return;
        }

        $original = $order->originalOrder;
        foreach ($original->orderItems as $oldItem) {
            $newItem = $oldItem->replicate(['order_id']);
            $newItem->order_id = $order->id;
            $newItem->save();

            foreach ($oldItem->specs as $oldSpec) {
                $newSpec = $oldSpec->replicate(['order_item_id']);
                $newSpec->order_item_id = $newItem->id;
                $newSpec->save();
            }
        }
        if ($original->orderAddress) {
            $newAddress = $original->orderAddress->replicate(['order_id']);
            $newAddress->order_id = $order->id;
            $newAddress->save();
        }

        if ($original->pickupContact) {
            $newPickup = $original->pickupContact->replicate(['order_id']);
            $newPickup->order_id = $order->id;
            $newPickup->save();
        }

    }
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
         if ($order->wasChanged('status') && $order->status === StatusEnum::CONFIRMED) {
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
