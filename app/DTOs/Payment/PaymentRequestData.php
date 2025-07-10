<?php

namespace App\DTOs\Payment;

use Illuminate\Support\Str;

class PaymentRequestData
{

    public function __construct(
        public $order,
        public $user,
        public $method,

    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            order: $data['order'],
            user: $data['user'],
            method: $data['method']
        );
    }

    public function toArray(): array
    {
        $amountCents = $this->order->total_price * 100;
        return [
            'amount' => $amountCents,
            'method' => $this->method,
            'billing' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email ?? 'invoice@more-english.net',
                'phone_number' => $this->user->phone_number ?? '01060525097',
                'country' => 'EG',
                'city' => 'Cairo',
                'state' => 'Cairo',
                'street' => 'Unknown',
                'building' => 'N/A',
                'floor' => 'N/A',
                'apartment' => 'N/A',
                'postal_code' => 'N/A',
            ],
            'customer' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'email' => $this->user->email ?? 'invoice@more-english.net',
            ],
            'items' => [[
                'name' => Str::limit('test', 50, ''),
                'amount' => $amountCents,
                'quantity' => 1,
            ]],
        ];
    }
}
