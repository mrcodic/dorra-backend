<?php

namespace App\DTOs\Payment\Paymob;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaymentRequestData
{

    public function __construct(
        public $order,
        public $user,
        public $guest,
        public $method,
        public $requestData,
    ){}

    public static function fromArray(array $data): self
    {
        return new self(
            order: $data['order'],
            user: $data['user'],
            guest: $data['guest'],
            method: $data['method'],
            requestData: Arr::get($data, 'requestData'),


        );
    }

    public function toArray(): array
    {
        $amountCents = (int) round($this->order->total_price)* 100 ;
        $baseItems = $this->order->orderItems->map(fn($item) => [
            'name' => Str::limit($item?->itemable->name ?? 'Item', 50, ''),
            'amount' => (int) round($item->sub_total)* 100,
            'quantity' => 1,
        ])->toArray();

        $extraItems = [];

        if ($this->order->delivery_amount > 0) {
            $extraItems[] = [
                'name' => Str::limit( 'Delivery Fee', 50, ''),
                'amount' => (int) round($this->order->delivery_amount)* 100,
                'quantity' => 1,
            ];
        }

        if ($this->order->tax_amount > 0) {
            $extraItems[] = [
                'name' => Str::limit('Tax', 50, ''),
                'amount' => (int) $this->order->tax_amount * 100  ,
                'quantity' => 1,
            ];
        }


        if ($this->order?->discount_amount > 0) {
            $extraItems[] = [
                'name' => Str::limit('Discount', 50, ''),
                'amount' => -(int) round($this->order->discount_amount)* 100,
                'quantity' => 1,
            ];
        }
        $allItems = array_merge($baseItems, $extraItems);
        return [
            'amount' =>collect($allItems)->sum('amount'),
            'method' => $this->method,
            'billing' => [
                'first_name' => $this->requestData?->first_name ?? 'John',
                'last_name' => $this->requestData?->last_name ?? 'Doe',
                'email' => $this->requestData?->email ?? 'johndoe@gmail.com',
                'phone_number' =>  $this->requestData?->full_phone_number ?? '01000000000',
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
                'first_name' => $this->requestData?->first_name ?? 'John',
                'last_name' => $this->requestData?->last_name ?? 'Doe',
                'email' => $this->requestData?->email ?? 'johndoe@gmail.com',
            ],
                'items' => $allItems,


        ];
    }
}
