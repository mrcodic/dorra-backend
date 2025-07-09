<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
            Invoice::create([
                'invoice_number' => $this->order->order_number,
                'order_id' => $this->order->id,
                'user_id' => $this->order->user_id,
                'design_id' => $this->order->orderItems->design_id,
                'quantity' => $this->order->orderItems->quantity,
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
