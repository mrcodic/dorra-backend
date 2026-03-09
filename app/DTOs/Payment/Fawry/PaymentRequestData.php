<?php

namespace App\DTOs\Payment\Fawry;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PaymentRequestData
{
    public function __construct(
        public $user,
        public $method,
        public $order = null,
        public $plan = null,
        public $guest = null,
        public $requestData = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user: $data['user'],
            method: $data['method'],
            order: Arr::get($data, 'order'),
            plan: Arr::get($data, 'plan'),
            guest: Arr::get($data, 'guest'),
            requestData: Arr::get($data, 'requestData'),
        );
    }

    public function toArray(): array
    {
        if ($this->order) {
            $items = $this->order->orderItems->map(fn ($item) => [
                'itemId' => (string) Str::uuid(),
                'description' => Str::limit($item?->itemable->name ?? 'Item', 50, ''),
                'price' => round((float) $item->sub_total, 2),
                'quantity' => 1,
            ])->toArray();

            if ($this->order->delivery_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Delivery Fee',
                    'price' => round((float) $this->order->delivery_amount, 2),
                    'quantity' => 1,
                ];
            }

            if ($this->order->tax_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Tax',
                    'price' => round((float) $this->order->tax_amount, 2),
                    'quantity' => 1,
                ];
            }

            if ($this->order->discount_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Discount',
                    'price' => round((float) -$this->order->discount_amount, 2),
                    'quantity' => 1,
                ];
            }

            $merchantRef = str_replace('#', '', $this->order->order_number);
        } else {
            $items = [[
                'itemId' => (string) Str::uuid(),
                'description' => Str::limit($this->plan->name ?? 'Plan', 50, ''),
                'price' => round((float) $this->plan->price, 2),
                'quantity' => 1,
            ]];

            $merchantRef = 'PLAN-' . Str::uuid();
        }

        $merchantCode = config('services.fawry.merchant_code');
        $returnUrl = config('services.fawry.redirection_url');
        $webhookUrl = config('services.fawry.webhook_url');

        $profileId = (string) ($this->user?->id ?? $this->guest?->id ?? Str::uuid());

        $phone = $this->requestData?->full_phone_number
            ?? $this->user?->phone_number
            ?? '01000000000';

        $customerName = trim(
            ($this->requestData?->first_name ?? '') . ' ' . ($this->requestData?->last_name ?? '')
        );

        if ($customerName === '') {
            $customerName = $this->user?->name ?? 'Customer';
        }

        $signature = generateFawrySignature(
            merchantCode: $merchantCode,
            merchantRefNum: $merchantRef,
            customerProfileId: $profileId,
            returnUrl: $returnUrl,
            items: $items
        );

        $language = app()->getLocale() === 'en' ? 'en-gb' : 'ar-eg';

        return [
            'merchantCode' => $merchantCode,
            'merchantRefNum' => $merchantRef,
            'customerMobile' => preg_replace('/^2(01)/', '$1', $phone),
            'customerEmail' => $this->requestData?->email ?? $this->user?->email ?? 'customer@example.com',
            'customerName' => $customerName,
            'customerProfileId' => $profileId,
            'paymentExpiry' => now()->addDays(2)->valueOf(),
            'language' => $language,
            'chargeItems' => $items,
            'paymentMethod' => (string) $this->method,
            'returnUrl' => $returnUrl,
            'authCaptureModePayment' => false,
            'signature' => $signature,
            'orderWebHookUrl' => $webhookUrl,
        ];
    }
}
