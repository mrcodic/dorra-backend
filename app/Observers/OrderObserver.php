<?php

namespace App\Observers;


use App\DTOs\Shipping\AddressDTO;
use App\Jobs\ProcessConfirmedOrderJob;

use App\Models\Admin;
use App\Models\Inventory;

use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;
use App\Notifications\OrderUpdated;
use App\Notifications\ShippingStatus;
use App\Notifications\UserRegistered;
use App\Services\Shipping\ShippingManger;
use Illuminate\Support\Facades\Notification;


class OrderObserver
{
    public function creating(Order $order)
    {
        $prefix = (string) (setting('order_format') ?: '#ORD');
        $order->order_number = sprintf('%s-%s-%s', $prefix, now()->format('Ymd'), $order->id);
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
        if ($order->wasChanged('status')){
            optional($order->user)->notify(new OrderUpdated($order));
            if (in_array($order->status, [StatusEnum::SHIPPED, StatusEnum::DELIVERED], true)) {

                $scenario = $order->status === StatusEnum::SHIPPED ? 'picked_up' : 'delivered';
//                Admin::select('id','first_name','last_name','email')
//                    ->chunkById(200, function ($admins) use ($order, $scenario) {
//                        Notification::send($admins, new ShippingStatus($order, $scenario));
//                    });
            }
            if ($order->status === StatusEnum::CONFIRMED)
            {
                ProcessConfirmedOrderJob::dispatch($order);
                CreateInvoiceJob::dispatch($order);
            }
//        if ($order->status == StatusEnum::PREPARED)
//        {
//            $shippingManager = app(ShippingManger::class);
//            $addressDto = AddressDTO::fromArray($order);
//            $shippingManager->driver('shipblu')->createShipment($addressDto, $order->id);
//        }

    if ($order->wasChanged('status') && $order->status === StatusEnum::PENDING) {
            $order->loadMissing(['paymentMethod']);

            if ($order->paymentMethod?->code === 'cash_on_delivery') {
                $order->update([
                    'payment_status' => \App\Enums\Payment\StatusEnum::PENDING
                ]);
            }
        }
        }

        if ($order->wasChanged('inventory_id'))
        {
            $inventory = Inventory::find($order->inventory_id);
            $inventory->update(["is_available" => false]);
        }


    }

    public function pivotSynced(Order $order, string $relation, array $changes): void
    {

        if ($relation !== 'inventories') return;

        if (!empty($changes['attached'])) {
            Inventory::whereIn('id', $changes['attached'])->update(['is_available' => 0]);
        }

        if (!empty($changes['detached'])) {
            Inventory::whereIn('id', $changes['detached'])->update(['is_available' => 1]);
        }
    }


    public function pivotAttached(Order $order, string $relation, array $ids, array $attributes): void
    {
        if ($relation !== 'inventories') return;
        Inventory::whereIn('id', $ids)->update(['is_available' => 0]);
    }


    public function pivotDetached(Order $order, string $relation, array $ids): void
    {
        if ($relation !== 'inventories') return;
        Inventory::whereIn('id', $ids)->update(['is_available' => 1]);
    }
}
