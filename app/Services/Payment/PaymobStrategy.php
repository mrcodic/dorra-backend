<?php

namespace App\Services\Payment;

use App\DTOs\Payment\PaymobIntentionData;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobStrategy implements PaymentGatewayStrategy
{
    public string $baseUrl;
    protected array $config;

    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository)
    {
        $this->baseUrl = config('services.paymob.base_url');
        $this->config = $gatewayRepository->config;
    }

    public function pay(array $data): array
    {
        $methodCode = $data['method'];
        $method = $this->gatewayRepository->query()->paymentMethods()->whereCode($methodCode)->first();

        if (!$method || !$method->active) {
            throw new \Exception("This payment method is not available");
        }

        return $this->createPaymentIntention($data, $methodCode);
    }

    protected function createPaymentIntention(array $data, string $paymentMethod): array
    {
        $integrationIds = [
            'credit_card' => $this->config['card_integration_id'],
            'wallet' => $this->config['wallet_integration_id'],
            'kiosk' => $this->config['kiosk_integration_id'],
        ];

        $dto = PaymobIntentionData::fromArray(
            data: [
                ...$data,
                'redirection_url' => $this->config['redirection_url'] ?? '',
                'notification_url' => $this->config['notification_url'] ?? '',
            ],
            integrationId: $integrationIds[$paymentMethod] ?? throw new \Exception("Invalid payment method"),
            currency: $this->config['currency'] ?? 'EGP'
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['secret_key'],
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/intention/', $dto->toArray());

        $result = $response->json();
        Log::info('Paymob Intention Response', ['response' => $result]);

        if ($response->failed() || empty($result['client_secret']) || empty($result['id'])) {
            Log::error('Failed to create payment intention', [
                'response' => $result,
                'status_code' => $response->status(),
            ]);
            throw new \Exception('Failed to create payment intention');
        }

        return [
            'checkout_url' => 'https://accept.paymob.com/unifiedcheckout/?publicKey='
                . $this->config['public_key']
                . '&clientSecret=' . $result['client_secret'],
            'order_id' =>  $result['intention_order_id'],
            'intention_id' => $result['id'],
            'client_secret' => $result['client_secret'],
        ];
    }

    public function refund(string $transactionId): bool
    {
        // Implement actual refund logic if needed
        return true;
    }
}
