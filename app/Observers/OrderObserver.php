<?php

namespace App\Observers;

use App\DTOs\Shipping\AddressDTO;
use App\Enums\Order\OrderTypeEnum;
use App\Jobs\ProcessConfirmedOrderJob;

use App\Models\Admin;
use App\Models\Inventory;

use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;
use App\Services\Shipping\ShippingManger;


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
            if ($order->orderAddress->type == OrderTypeEnum::SHIPPING)
            {
                $shippingManager = app(ShippingManger::class);
                $addressDto = AddressDTO::fromArray($order);
                $shippingManager->driver('shipblu')->createShipment($addressDto, $order->id);
            }
            ProcessConfirmedOrderJob::dispatch($order);
            CreateInvoiceJob::dispatch($order);
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
