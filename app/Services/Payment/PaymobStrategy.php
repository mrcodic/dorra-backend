<?php

namespace App\Services\Payment;

use App\DTOs\Payment\PaymobIntentionData;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobStrategy implements PaymentGatewayStrategy
{
    public string $baseUrl;
    protected  $config;

    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository,public $gatewayCode)
    {
        $this->baseUrl = config('services.paymob.base_url');

        $gateway = $this->gatewayRepository->query()
            ->whereCode($this->gatewayCode)
            ->active()
            ->first();

        if (!$gateway) {
            throw new \Exception("Payment gateway [{$this->gatewayCode}] not found or inactive.");
        }

        $this->config = config('services.paymob');
    }

    public function pay(array $data): array
    {
        $method = $data['method'];

        if (!$method || !$method->active) {
            throw new \Exception("This payment method is not available");
        }

        return $this->createPaymentIntention($data, $method->code);
    }

    protected function createPaymentIntention(array $data, string $paymentMethod): array
    {
        $integrationIds = [
            'paymob_card' => $this->config['card_integration_id'],
            'paymob_wallet' => $this->config['wallet_integration_id'],
            'paymob_kiosk' => $this->config['kiosk_integration_id'],
        ];

        $dto = PaymobIntentionData::fromArray(
            data: [
                ...$data,
                'redirection_url' => $this->config['redirection_url'] ?? '',
                'notification_url' => $this->config['notification_url'] ?? '',
            ],
            integrationId: (int) $integrationIds[$paymentMethod] ?? throw new \Exception("Invalid payment method"),
            currency: $this->config['currency'] ?? 'EGP'
        );

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['secret_key'],
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/intention/', $dto->toArray());

        $result = $response->json();
        dd($result);
        Log::info('Paymob Intention Response', ['response' => $result]);

        if ($response->failed() || empty($result['client_secret']) || empty($result['id'])) {
            Log::error('Failed to create payment intention', [
                'response' => $result,
                'status_code' => $response->status(),
            ]);
            throw new \Exception('Failed to create payment intention');
        }

        return [
            'checkout_url' => $this->baseUrl .'/unifiedcheckout/?publicKey='
                . $this->config['public_key']
                . '&clientSecret=' . $result['client_secret'],
            'order_id' =>  $result['intention_order_id'],
            'intention_id' => $result['id'],
            'client_secret' => $result['client_secret'],
        ];
    }

    public function refund(string $transactionId): bool
    {
        return true;
    }
}
