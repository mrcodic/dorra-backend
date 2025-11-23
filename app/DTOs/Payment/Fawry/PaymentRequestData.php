<?php

namespace App\DTOs\Payment\Fawry;

use Illuminate\Support\Str;

class PaymentRequestData
{
    public function __construct(
        public $order,
        public $requestData,
        public $user,
        public $guest,
        public $method,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            order: $data['order'],
            requestData: $data['requestData'],
            user: $data['user'],
            guest: $data['guest'],
            method: $data['method'],
        );
    }

    public function toArray(): array
    {
        $baseItems = $this->order->orderItems->map(fn($item) => [
            'itemId' => (string)Str::uuid(),
            'description' => Str::limit($item?->itemable->name ?? 'Item', 50, ''),
            'price' => (float)number_format((float)$item->sub_total, 2, '.', ''),
            'quantity' => 1.0,
        ])->toArray();

        $extraItems = [];

        if ($this->order->delivery_amount > 0) {
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Delivery Fee',
                'price' => (float)number_format((float)$this->order->delivery_amount, 2, '.', ''),
                'quantity' => 1.0,
            ];
        }

        if (setting('tax') > 0) {
            $taxAmount = getPriceAfterTax(setting('tax'), $this->order->subtotal);
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Tax',
                'price' => (float)number_format((float)$taxAmount, 2, '.', ''),
                'quantity' => 1.0,
            ];
        }

//        if ($this->order?->discount_amount > 0) {
//            $extraItems[] = [
//                'itemId' => (string)Str::uuid(),
//                'description' => 'Discount',
//                'price' => (float)number_format((float)(-$this->order->discount_amount), 2, '.', ''),
//                'quantity' => 1,
//            ];
//        }

        $allItems = array_merge($baseItems, $extraItems);

        $merchantCode = config('services.fawry.merchant_code');
        $merchantRef = $this->order->order_number;
        $returnUrl = config('services.fawry.redirection_url');
        $profileId = $this->user?->id ?? $this->guest?->id ?? '';

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
            'customerProfileId' => $profileId,
            'customerName' => trim($this->requestData->first_name . ' ' . $this->requestData->last_name),
            'paymentExpiry' => now()->addDays(2)->valueOf(),
//            'customerMobile' => $this->requestData->full_phone_number,
            'customerEmail' => $this->requestData->email,
            'language' => $language,
            'returnUrl' => $returnUrl,
            'chargeItems' => $allItems,
            'paymentMethod' => $this->method,
            'authCaptureModePayment' => false,
            'signature' => $signature,
        ];
    }
}
