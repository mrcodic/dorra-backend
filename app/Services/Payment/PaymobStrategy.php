<?php

namespace App\Services\Payment;

use AllowDynamicProperties;
use App\DTOs\Payment\PaymobIntentionData;
use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[AllowDynamicProperties]
class PaymobStrategy implements PaymentGatewayStrategy
{
    protected string $baseUrl;
    protected string $callback;
    protected array $config;
    protected array $integrationIds;

    public function __construct(
        public PaymentGatewayRepositoryInterface $gatewayRepository,
        public string $gatewayCode
    ) {
        $this->config = config('services.paymob');
        $this->baseUrl = $this->config['base_url'];
        $this->callback = $this->config['callback'];

        $this->integrationIds = [
            'paymob_card' => $this->config['card_integration_id'],
            'paymob_wallet' => $this->config['wallet_integration_id'],
            'paymob_kiosk' => $this->config['kiosk_integration_id'],
        ];

        $gateway = $this->gatewayRepository->query()
            ->whereCode($this->gatewayCode)
            ->active()
            ->first();

        if (!$gateway) {
            throw new \Exception("Payment gateway [{$this->gatewayCode}] not found or inactive.");
        }
    }

    public function pay(array $payload, array $data): array
    {
        $method = $payload['method'] ?? null;

        if (!$method || !$method->active) {
            throw new \Exception("This payment method is not available");
        }

        return $this->createPaymentIntention($payload, $data, $method->code);
    }

    protected function createPaymentIntention(array $payload, array $data, string $paymentMethod): array
    {
        if (!isset($this->integrationIds[$paymentMethod])) {
            throw new \Exception("Invalid or unsupported payment method: $paymentMethod");
        }

        $integrationId = (int)$this->integrationIds[$paymentMethod];

        $dto = PaymobIntentionData::fromArray(
            data: [
                ...$payload,
                'redirection_url' => $this->config['redirection_url'] ?? '',
                'notification_url' => $this->config['notification_url'] ?? '',
            ],
            integrationId: $integrationId,
            currency: $this->config['currency'] ?? 'EGP'
        );

        Log::info('Creating Paymob intention', [
            'integration_id' => $integrationId,
            'payment_method' => $paymentMethod,
            'request_payload' => $dto->toArray(),
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['secret_key'],
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/intention/', $dto->toArray());

        $result = $response->json();
        dd($result);

        Log::info('Paymob intention response', [
            'status_code' => $response->status(),
            'response' => $result,
        ]);

        if ($response->failed() || empty($result['client_secret']) || empty($result['id'])) {
            Log::error('Failed to create Paymob intention', [
                'status_code' => $response->status(),
                'response' => $result,
            ]);

            throw new \Exception('Failed to create payment intention');
        }

        $orderData = [
            'checkout_url' => $this->baseUrl . '/unifiedcheckout/?publicKey='
                . $this->config['public_key']
                . '&clientSecret=' . $result['client_secret'],
            'order_id' => $result['intention_order_id'],
            'amount' => $result['intention_detail']['amount'],
            'intention_id' => $result['id'],
            'client_secret' => $result['client_secret'],
        ];

        return $this->storeTransaction($orderData, $data, $paymentMethod);
    }

    public function storeTransaction(array $orderData, array $data, string $paymentMethod): array
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
        // You can implement refund logic here if needed
        return true;
    }
}
