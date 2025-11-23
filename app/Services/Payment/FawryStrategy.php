<?php

namespace App\Services\Payment;

use AllowDynamicProperties;
use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;

#[AllowDynamicProperties] class FawryStrategy implements PaymentGatewayStrategy
{

    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository, public $gatewayCode)
    {
        $this->baseUrl = config('services.fawry.base_url');
        $this->callback = config('services.fawry.redirection_url');
        $gateway = $this->gatewayRepository->query()
            ->whereCode($this->gatewayCode)
            ->active()
            ->first();

        if (!$gateway) {
            throw new \Exception("Payment gateway [{$this->gatewayCode}] not found or inactive.");
        }

        $this->config = config('services.fawry');
    }

    public function pay(array $payload, ?array $data): false|array
    {
        $url = $this->baseUrl.'fawrypay-api/api/payments/init';

        $response = Http::asJson()->post($url, $payload);

        dd($response->status(), $response->header('Content-Type'), $response->body(),$payload);


    }

    public function storeTransaction($orderData, $data, $paymentMethod): array
    {
        $transaction = Transaction::create([
            'order_id' => $data['order']->id,
            'amount' => $orderData['amount'],
            'payment_method' => $paymentMethod,
            'payment_status' => StatusEnum::PENDING,
            'transaction_id' => $orderData['order_id'],
            'response_message' => json_encode($orderData),
            'success_url' => request()->success_url ?? $this->callback,
            'failure_url' => request()->failure_url ?? $this->callback,
            'pending_url' => request()->pending_url ?? $this->callback,
            'expiration_date' => now()->addDays(2),
        ]);

        return [
            'transaction_id' => $transaction->transaction_id,
            'amount' => $orderData['amount'],
            'payment_url' => $orderData['checkout_url'],
        ];
    }


    public function refund(string $transactionId): bool
    {
        return true;
    }
}
