<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\JobTicket;
use App\Services\BarcodeService;
use App\Enums\Payment\StatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessConfirmedOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $order = $this->order->loadMissing(['paymentMethod', 'orderItems']);
        $svc = app(BarcodeService::class);

        $jobsDataUrl = route("jobs.index", ['search_value' => $order->order_number]);

        // Generate QR for the order itself
        $svc->savePngQR('orders', $jobsDataUrl, scale: 6);
        $svc->saveSvgQR('orders', $jobsDataUrl, width: 4, height: 4);

        if ($order->paymentMethod?->code === 'cash_on_delivery') {
            $order->updateQuietly([
                'payment_status' => StatusEnum::PAID
            ]);
        }

        // Create job tickets for each item
        foreach ($order->orderItems as $orderItem) {
            $sequence = JobTicket::where('order_item_id', $orderItem->id)->count() + 1;

            $ticket = JobTicket::firstOrCreate(
                ['order_item_id' => $orderItem->id],
                [
                    'code' => sprintf(
                        "JT-%s-%d-%d-%02d",
                        now()->format('Ymd'),
                        $order->id,
                        $orderItem->id,
                        $sequence
                    ),
                    'specs' => $orderItem->specs?->map(fn($item) => [
                        'spec_name' => $item->spec_name,
                        'option_name' => $item->option_name,
                    ])->toArray(),
                ]
            );

            if ($ticket->wasRecentlyCreated) {
                $svc->savePng1D('job-tickets', $ticket->code, 'C128', scale: 4, height: 120);
                $svc->saveSvg1D('job-tickets', $ticket->code, 'C128', width: 2, height: 60, withText: true);
                $svc->savePngQR('job-tickets', $ticket->code, scale: 6);
                $svc->saveSvgQR('job-tickets', $ticket->code, width: 4, height: 4);
            }
        }

//        DB::transaction(function () use ($order) {
            $inventory = Inventory::query()
                ->whereNotNull('parent_id')
                ->where('is_available', 1)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                return;
            }


          $order->inventories()->attach([$inventory->id]);
            Inventory::where('id', $inventory->id)->update(['is_available' => 0]);
//        });

    }
}
