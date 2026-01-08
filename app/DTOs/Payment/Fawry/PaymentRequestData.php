<?php

namespace App\DTOs\Payment\Fawry;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaymentRequestData
{
    public function __construct(
        public $order,
        public $user,
        public $guest,
        public $method,
        public $requestData = null,
    )
    {
    }

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
        $baseItems = $this->order->orderItems->map(fn($item) => [
            'itemId' => (string)Str::uuid(),
            'description' => Str::limit($item?->itemable->name ?? 'Item', 50, ''),
            'price' => (float)number_format((float)$item->sub_total, 2, '.', ''),
            'quantity' => 1,
        ])->toArray();

        $extraItems = [];

        if ($this->order->delivery_amount > 0) {
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Delivery Fee',
                'price' => (float)number_format((float)$this->order->delivery_amount, 2, '.', ''),
                'quantity' => 1,
            ];
        }

        if ($this->order->tax_amount > 0) {
            $taxAmount = $this->order->tax_amount;
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Tax',
                'price' => (float)number_format((float)$taxAmount, 2, '.', ''),
                'quantity' => 1,
            ];
        }

        if ($this->order?->discount_amount > 0) {
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Discount',
                'price' => (float)number_format((float)(-$this->order->discount_amount), 2, '.', ''),
                'quantity' => 1,
            ];
        }

        $allItems = array_merge($baseItems, $extraItems);

        $merchantCode = config('services.fawry.merchant_code');
        $merchantRef = str_replace('#', '', $this->order->order_number);
        $returnUrl = (string)config('services.fawry.redirection_url');
        $webhookUrl = (string)config('services.fawry.webhook_url');
        $profileId = (string)$this->user?->id ?? $this->guest?->id ?? '';
        $phone = $this->requestData?->full_phone_number ?? $this->user?->phone_number ?? '01000000000';
        $signature = generateFawrySignature(
            merchantCode: $merchantCode,
            merchantRefNum: $merchantRef,
            customerProfileId: $profileId,
            returnUrl: $returnUrl,
            items: $allItems
        );

        $language = app()->getLocale() === 'en' ? 'en-gb' : 'ar-eg';

        return [
            'merchantCode' => $merchantCode,
            'merchantRefNum' => $merchantRef,
            'customerMobile' => preg_replace('/^2(01)/', '$1', (string)$phone) ?? '01000000000',
            'customerEmail' => $this->requestData?->email ?? $this->user?->email ?? 'johndoe@gmail.com',
            'customerName' => trim($this->requestData?->first_name . ' ' . $this->requestData?->last_name)
                ?? $this->user?->name ?? 'John Doe',
            'customerProfileId' => $profileId,
            'paymentExpiry' => now()->addDays(2)->valueOf(),
            'language' => $language,
            'chargeItems' => $allItems,

            'paymentMethod' => (string)$this->method,
            'returnUrl' => $returnUrl,
            'authCaptureModePayment' => false,
            'signature' => $signature,
            'orderWebHookUrl' => $webhookUrl,
        ];
    }
}
