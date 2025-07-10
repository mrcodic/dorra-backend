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
        $amountCents = (int) $this->order->total_price * 100;
        $baseItems = $this->order->orderItems->map(fn($item) => [
            'name' => Str::limit($item->design?->name ?? 'Item', 50, ''),
            'amount' => (int) $item->total_price * 100,
            'quantity' => $item->design?->quantity ?? 1,
        ])->toArray();

        $extraItems = [];

        if (setting('delivery') > 0) {
            $extraItems[] = [
                'name' => 'Delivery Fee',
                'amount' => (int) setting('delivery')  * 100,
                'quantity' => 1,
            ];
        }

        if (setting('tax') > 0) {
            $extraItems[] = [
                'name' => 'Tax',
                'amount' => (int) getPriceAfterTax(setting('tax'), $this->order->subtotal) * 100,
                'quantity' => 1,
            ];
        }

        if ($this->order->discount_amount > 0) {
            $extraItems[] = [
                'name' => 'Discount',
                'amount' =>  $this->order->discount_amount  * 100,
                'quantity' => 1,
            ];
        }
        $allItems = array_merge($baseItems, $extraItems);
//        dd($allItems,$amountCents);
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
                'items' => $allItems,


        ];
    }
}
