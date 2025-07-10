<?php

namespace App\Services\Payment;

use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;

class PaymentGatewayFactory
{
    public function __construct(public PaymentGatewayRepositoryInterface $paymentGatewayRepository){}

    public function make($gatewayCode)
    {
        $gateway = $this->paymentGatewayRepository->query()->whereCode($gatewayCode)->active()->first();
        if (!$gateway) {
            throw new \Exception("Gateway not found");
        }
        return match ($gatewayCode) {
            'paymob' => new PaymobStrategy( $this->paymentGatewayRepository, $gatewayCode),
            default => throw new \Exception("Unsupported gateway"),
        };
    }
}
