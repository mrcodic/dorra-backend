<?php

namespace App\DTOs\Payment;

class PaymobIntentionData
{
    public function __construct(
        public int $amount,
        public string $currency,
        public array $paymentMethods,
        public array $billingData,
        public array $customer,
        public array $items,
        public string $redirectionUrl,
        public string $notificationUrl,
        public int $expiration = 172800, // Default 2 days
    ) {}

    public static function fromArray(array $data, string $integrationId, string $currency = 'EGP'): self
    {
        return new self(
            amount: $data['amount'] * 100,
            currency: $currency,
            paymentMethods: [$integrationId],
            billingData: $data['billing'] ?? [],
            customer: $data['customer'] ?? [],
            items: $data['items'] ?? [],
            redirectionUrl: $data['redirection_url'] ?? '',
            notificationUrl: $data['notification_url'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_methods' => $this->paymentMethods,
            'billing_data' => $this->billingData,
            'customer' => $this->customer,
            'items' => $this->items,
            'redirection_url' => $this->redirectionUrl,
            'notification_url' => $this->notificationUrl,
            'expiration' => $this->expiration,
        ];
    }
}
