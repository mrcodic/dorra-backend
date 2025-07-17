<?php

namespace App\DTOs\Payment;

use Illuminate\Support\Str;

class PaymentRequestData
{

    public function __construct(
        public $order,
        public $user,
        public $guest,
        public $method,

    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            order: $data['order'],
            user: $data['user'],
            guest: $data['guest'],
            method: $data['method']
        );
    }

    public function toArray(): array
    {

        $amountCents = (int) ($this->order->total_price * 100);
        $baseItems = $this->order->orderItems->map(fn($item) => [
            'name' => Str::limit($item->design?->name ?? 'Item', 50, ''),
            'amount' => (int) round($item->total_price * 100),
            'quantity' => 1,
        ])->toArray();

        $extraItems = [];

        if (setting('delivery') > 0) {
            $extraItems[] = [
                'name' => Str::limit( 'Delivery Fee', 50, ''),
                'amount' => (int) round(setting('delivery')  * 100),
                'quantity' => 1,
            ];
        }

        if (setting('tax') > 0) {
            $extraItems[] = [
                'name' => Str::limit( 'Tax', 50, ''),
                'amount' => (int) round((int) getPriceAfterTax(setting('tax'), $this->order->subtotal) * 100),
                'quantity' => 1,
            ];
        }
        if ($this->order?->discount_amount > 0) {
            $extraItems[] = [
                'name' => Str::limit('Discount', 50, ''),
                'amount' => -(int) round($this->order->discount_amount * 100),
                'quantity' => 1,
            ];
        }
        $allItems = array_merge($baseItems, $extraItems);

        return [
            'amount' => $amountCents,
            'method' => $this->method,
            'billing' => [
                'first_name' => $this->user?->first_name ?? $this->guest?->first_name,
                'last_name' => $this->user?->last_name ?? $this->guest?->last_name,
                'email' => $this->user?->email ?? $this->guest?->email,
                'phone_number' =>  $this->user?->phone_number ?? $this->guest?->phone,
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
                'first_name' =>  $this->user?->first_name ?? $this->guest?->first_name,
                'last_name' =>  $this->user?->last_name ?? $this->guest?->last_name,
                'email' => $this->user?->email ?? $this->guest?->email,
            ],
                'items' => $allItems,


        ];
    }
}
