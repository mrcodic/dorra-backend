<?php

namespace App\Services\Payment;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;

class PaymobStrategy implements PaymentGatewayStrategy
{
    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository){}

    public function pay(array $data): array
    {
       $methodCode = $data['method'];
       $method = $this->gatewayRepository->query()->paymentMethods()->whereCode($methodCode)->first();
        if (!$method || !$method->active) {
            throw new \Exception("This payment method is not available");
        }

        return match ($methodCode) {
            'credit_card' => $this->payWithCard($data),
            'wallet'      => $this->payWithWallet($data),
            'kiosk'       => $this->payWithKiosk($data),
            default       => throw new \Exception("Unsupported method for Paymob")
        };
    }

    protected function payWithCard(array $data): array
    {
        return [
            'redirect_url' => 'https://accept.paymobsolutions.com/iframe/' . $this->gatewayRepository->config['iframe_id']
        ];
    }

    protected function payWithWallet(array $data): array
    {
        return [
            'wallet_reference' => 'dummy_wallet_ref'
        ];
    }

    protected function payWithKiosk(array $data): array
    {
        return [
            'bill_reference' => 'dummy_kiosk_ref'
        ];
    }
    public function refund(string $transactionId): bool
    {
        return true;
    }
}
