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

class ProcessPreparedOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $afterCommit = true;
    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $order = Order::query()
            ->with(['orderItems'])
            ->find($this->order->getKey());

        if (! $order) return;


    }

}
