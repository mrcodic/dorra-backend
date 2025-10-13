<?php

namespace App\Observers;

use App\Jobs\ProcessConfirmedOrderJob;
use App\Jobs\ProcessPreparedOrderJob;
use App\Jobs\ProcessShippedOrderJob;
use App\Models\Admin;
use App\Models\Inventory;
use App\Models\JobTicket;
use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;
use App\Models\OrderItem;
use App\Services\BarcodeService;
use Illuminate\Support\Facades\DB;


class OrderObserver
{
    public function creating(Order $order)
    {
        $now = now();
        $dateString = $now->format('d-m-Y');
        $order->order_number = "#ORD-{$dateString}-" . mt_rand(100, 999);
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (request()->user() instanceof Admin) {
            $order->update(["status" => StatusEnum::CONFIRMED]);
        }

    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('inventory_id'))
        {
            $inventory = Inventory::find($order->inventory_id);
            $inventory->update(["is_available" => false]);
        }
        if ($order->wasChanged('status') && $order->status === StatusEnum::CONFIRMED) {
            ProcessConfirmedOrderJob::dispatch($order);
            CreateInvoiceJob::dispatch($order);
        }
        if ($order->wasChanged('status') && $order->status === StatusEnum::PREPARED) {
            if ($order->inventory_id) {
                return;
            }

            DB::transaction(function () use ($order) {
                $inventory = Inventory::query()
                    ->whereNotNull('parent_id')
                    ->where('is_available', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    return;
                }
                $inventory->update(['is_available' => 0]);
                $order->update(['inventory_id' => $inventory->id]);
            });
        }

        if ($order->wasChanged('status') && $order->status === StatusEnum::PENDING) {
            $order->loadMissing(['paymentMethod']);

            if ($order->paymentMethod?->code === 'cash_on_delivery') {
                $order->update([
                    'payment_status' => \App\Enums\Payment\StatusEnum::PENDING
                ]);
            }
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
