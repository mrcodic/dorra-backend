<?php

namespace App\Services\Payment;

use AllowDynamicProperties;
use App\DTOs\Payment\PaymobIntentionData;
use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[AllowDynamicProperties] class PaymobStrategy implements PaymentGatewayStrategy
{

    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository, public $gatewayCode)
    {
        $this->baseUrl = config('services.paymob.base_url');
        $this->callback = config('services.paymob.callback');
        $gateway = $this->gatewayRepository->query()
            ->whereCode($this->gatewayCode)
            ->active()
            ->first();

        if (!$gateway) {
            throw new \Exception("Payment gateway [{$this->gatewayCode}] not found or inactive.");
        }

        $this->config = config('services.paymob');
    }

    public function pay(array $payload, array $data): false|array
    {
        $method = $payload['method'];

        if (!$method || !$method->active) {
            throw new \Exception("This payment method is not available");
        }

        return $this->createPaymentIntention($payload, $data, $method->code);
    }

    protected function createPaymentIntention(array $payload, array $data, string $paymentMethod): false|array
    {
        $integrationIds = [
            'paymob_card' => $this->config['card_integration_id'],
            'paymob_wallet' => $this->config['wallet_integration_id'],
            'paymob_kiosk' => $this->config['kiosk_integration_id'],
        ];

        $dto = PaymobIntentionData::fromArray(
            data: [
                ...$payload,
                'redirection_url' => $this->config['redirection_url'] ?? '',
                'notification_url' => $this->config['notification_url'] ?? '',
            ],
            integrationId: (int)$integrationIds[$paymentMethod] ?? throw new \Exception("Invalid payment method"),
            currency: $this->config['currency'] ?? 'EGP'
        );
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config['secret_key'],
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v1/intention/', $dto->toArray());
        $result = $response->json();
     
        Log::error('Failed to create payment intention', [
            'response' => $result,
            'vzx' => $response,
        ]);
        if ($response->failed() || empty($result['client_secret']) || empty($result['id'])) {
            Log::error('Failed to create payment intention', [
                'response' => $result,
                'status_code' => $response->status(),
            ]);
            return false;
        }
        $cart = Arr::get($data, 'cart');
        $cart?->items()->delete();
        $cart?->update(['price' => 0, 'discount_amount' => 0, 'discount_code_id' => null]);
        if ($cart && $cart->discountCode) {
            $cart->discountCode->increment('used');
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
