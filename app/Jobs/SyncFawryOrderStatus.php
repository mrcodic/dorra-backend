<?php

namespace App\Jobs;

use App\Enums\Payment\StatusEnum;
use App\Models\Order;
use App\Services\Payment\FawryStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncFawryOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FawryStrategy $fawry): void
    {
        Order::query()
            ->where('payment_status', StatusEnum::PENDING)
            ->whereHas('paymentMethod.paymentGateway', fn ($q) => $q->where('code', 'fawry'))
            ->whereHas('transactions', fn ($q) => $q->where('payment_status', StatusEnum::PENDING))
            ->where('created_at', '>=', now()->subHours(48))
            ->with(['transactions', 'paymentMethod.paymentGateway'])
            ->chunkById(100, function ($orders) use ($fawry) {
                foreach ($orders as $order) {
                    $this->syncOrder($order, $fawry);
                }
            });
    }

    private function syncOrder(Order $order, FawryStrategy $fawry): void
    {
        $transaction = $order->transactions()
            ->where('payment_status', StatusEnum::PENDING)
            ->latest()
            ->first();

        if (!$transaction?->transaction_id) {
            Log::info('Skipping order: missing transaction_id', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_id' => $transaction?->id,
            ]);
            return;
        }

        try {
            $fawryStatus = $fawry->getStatus($transaction->transaction_id);

            Log::info('Fawry raw status fetched', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_id' => $transaction->id,
                'reference' => $transaction->transaction_id,
                'fawry_status' => $fawryStatus,
            ]);

            $mappedStatus = $this->mapStatus($fawryStatus);

            if ($mappedStatus === $transaction->payment_status) {
                Log::info('No status change detected', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'transaction_id' => $transaction->id,
                    'current_status' => $transaction->payment_status instanceof \BackedEnum
                        ? $transaction->payment_status->value
                        : $transaction->payment_status,
                    'fawry_status' => $fawryStatus,
                    'mapped_status' => $mappedStatus->value,
                ]);
                return;
            }

            $transaction->update([
                'payment_status' => $mappedStatus,
            ]);

            $order->update([
                'payment_status' => $mappedStatus,
            ]);

            Log::info('Fawry status synced', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_id' => $transaction->id,
                'reference' => $transaction->transaction_id,
                'fawry_status' => $fawryStatus,
                'mapped_status' => $mappedStatus->value,
            ]);
        } catch (\Throwable $e) {
            Log::error('Fawry sync failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'transaction_id' => $transaction?->id,
                'reference' => $transaction?->transaction_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }

    private function mapStatus(string $fawryStatus): StatusEnum
    {
        return match (strtoupper(trim($fawryStatus))) {
            'PAID' => StatusEnum::PAID,
            'CANCELLED' => StatusEnum::CANCELLED,
            'REFUNDED', 'PARTIAL_REFUNDED' => StatusEnum::REFUNDED,
            'EXPIRED', 'FAILED', 'NOT_PAID' => StatusEnum::FAILED,
            default => StatusEnum::PENDING,
        };
    }
}
