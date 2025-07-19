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
    public function __construct(public Order $order)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {


        $invoice = Invoice::updateOrCreate([
            'order_id' => $this->order->id,
        ], [
            'invoice_number' => $this->order->order_number,
            'user_id' => $this->order->user_id,
            'subtotal' => $this->order->subtotal,
            'discount_amount' => $this->order->discount_amount,
            'delivery_amount' => $this->order->delivery_amount,
            'tax_amount' => $this->order->tax_amount,
            'total_price' => $this->order->total_price,
            'status' => $this->order->status,
            'issued_date' => now(),
        ]);

        $designIds = $this->order->orderItems->pluck('design_id')->filter()->unique();

        if ($designIds->isNotEmpty()) {
            $invoice->designs()->sync($designIds);
        }
    }
}
