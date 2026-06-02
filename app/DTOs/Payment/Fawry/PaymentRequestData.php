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
            $items = $this->order->orderItems->map(function ($item) {
                return [
                    'itemId' => (string) Str::uuid(),
                    'description' => Str::limit($item?->itemable?->name ?? 'Item', 50, ''),
                    'price' => round((float) $item->sub_total, 2),
                    'quantity' => 1,
                ];
            })->toArray();

            if ((float) $this->order->delivery_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Delivery Fee',
                    'price' => round((float) $this->order->delivery_amount, 2),
                    'quantity' => 1,
                ];
            }

            if ((float) $this->order->tax_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Tax',
                    'price' => round((float) $this->order->tax_amount, 2),
                    'quantity' => 1,
                ];
            }

            if ((float) $this->order->discount_amount > 0) {
                $items[] = [
                    'itemId' => (string) Str::uuid(),
                    'description' => 'Discount',
                    'price' => round((float) -$this->order->discount_amount, 2),
                    'quantity' => 1,
                ];
            }

            $merchantRef = str_replace('#', '', (string) $this->order->order_number);
        } else {
            $items = [[
                'itemId' => (string) Str::uuid(),
                'description' => Str::limit($this->plan?->name ?? 'Plan', 50, ''),
                'price' => round((float) $this->plan?->price, 2),
                'quantity' => 1,
            ]];

            $merchantRef = 'PLAN-' . (string) Str::uuid();
        }

        $merchantCode = config('services.fawry.merchant_code');
        $baseReturnUrl = config('services.fawry.redirection_url');
        $webhookUrl = config('services.fawry.webhook_url');

        $returnUrl = $this->appendQueryToUrl($baseReturnUrl, [
            'merchantRef' => $merchantRef,
        ]);

        $profileId = (string) (
            $this->user?->id
            ?? $this->guest?->id
            ?? Str::uuid()
        );

        $phone = $this->requestData?->full_phone_number
            ?? $this->user?->phone_number
            ?? '01000000000';

        $phone = $this->normalizeEgyptPhone($phone);

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
            'customerMobile' => $phone,
            'customerEmail' => $this->requestData?->email ?? $this->user?->email ?? 'customer@example.com',
            'customerName' => $customerName,
            'customerProfileId' => $profileId,
            'paymentExpiry' => now()->addHour()->valueOf(),
            'language' => $language,
            'chargeItems' => $items,
            'paymentMethod' => (string) $this->method,
            'returnUrl' => $returnUrl,
            'authCaptureModePayment' => false,
            'signature' => $signature,
            'orderWebHookUrl' => $webhookUrl,
        ];
    }

    private function appendQueryToUrl(?string $url, array $query): string
    {
        $url = rtrim((string) $url, '?&');

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query);
    }

    private function normalizeEgyptPhone(?string $phone): string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);


        if (str_starts_with($phone, '2') && str_starts_with(substr($phone, 1), '01')) {
            return substr($phone, 1);
        }
        if (str_starts_with($phone, '1')) {
            return '0' . $phone;
        }

        return $phone ?: '01000000000';
    }
}
