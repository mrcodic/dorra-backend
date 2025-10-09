<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\JobTicket;
use App\Models\Order;
use App\Jobs\CreateInvoiceJob;
use App\Enums\Order\StatusEnum;
use App\Models\OrderItem;
use App\Services\BarcodeService;


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
    public function updated(Order $order,BarcodeService $svc): void
    {
        if ($order->wasChanged('status') && $order->status === StatusEnum::CONFIRMED) {
            $order->loadMissing(['paymentMethod','orderItems']);
            $jobsDataUrl = route("jobs.index",['search_value'=> $order->order_number]);
            // 1D
            $svc->savePng1D('orders',$jobsDataUrl, 'C128', scale: 4, height: 120);
            $svc->saveSvg1D('orders',$jobsDataUrl, 'C128', width: 2, height: 60, withText: true);
            // 2D (QR)
            $svc->savePngQR('orders',$jobsDataUrl, scale: 6);
            $svc->saveSvgQR('orders',$jobsDataUrl, width: 4, height: 4);

            if ($order->paymentMethod?->code === 'cash_on_delivery') {
                $order->update([
                    'payment_status' => \App\Enums\Payment\StatusEnum::PAID
                ]);
            }


            $tickets = $order->orderItems->map(function (OrderItem $orderItem) use ($order) {

                $sequence = JobTicket::where('order_item_id', $orderItem->id)->count() + 1;

                return JobTicket::firstOrCreate(
                    ['order_item_id' => $orderItem->id],
                    [
                        'code' => sprintf(
                            "JT-%s-%d-%d-%02d",
                            now()->format('Ymd'),
                            $order->id,
                            $orderItem->id,
                            $sequence
                        ),
                        'specs' => $orderItem->specs?->map(fn ($item) => [
                            'spec_name'   => $item->spec_name,
                            'option_name' => $item->option_name,
                        ])->toArray(),
                    ]
                );
            });


            if ($tickets->isNotEmpty()) {
                /** @var \App\Services\BarcodeService $svc */
//                $svc = app(\App\Services\BarcodeService::class);

                foreach ($tickets as $ticket) {
                    if ($ticket->wasRecentlyCreated) {
                        // 1D
                        $svc->savePng1D('job-tickets',$ticket->code, 'C128', scale: 4, height: 120);
                        $svc->saveSvg1D('job-tickets',$ticket->code, 'C128', width: 2, height: 60, withText: true);

                        // 2D (QR)
                        $qrPayload = $ticket->code;
                        $svc->savePngQR('job-tickets',$qrPayload, scale: 6);
                        $svc->saveSvgQR('job-tickets',$qrPayload, width: 4, height: 4);
                    }
                }
            }



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
