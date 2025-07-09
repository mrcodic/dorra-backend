<?php

namespace App\Jobs;

use Log;
use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order) {}

    /**
     * Execute the job.
     */
public function handle(): void
{
    if (!$this->order->invoice) {
        $orderItem = $this->order->orderItems->first();

        if (!$orderItem) {
            Log::warning("Order #{$this->order->id} has no order items.");
            return;
        }

        Invoice::updateOrCreate([
            'order_id' => $this->order->id,
        ], [
            'invoice_number' => $this->order->order_number,
            'user_id' => $this->order->user_id,
            'design_id' => $orderItem->design_id,
            'quantity' => $orderItem->quantity,
            'subtotal' => $this->order->subtotal,
            'discount_amount' => $this->order->discount_amount,
            'delivery_amount' => $this->order->delivery_amount,
            'tax_amount' => $this->order->tax_amount,
            'total_price' => $this->order->total_price,
            'status' => $this->order->status,
            'issued_date' => now(),
        ]);
    }
}
}