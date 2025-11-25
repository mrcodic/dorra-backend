<?php

namespace App\DTOs\Payment\Fawry;

use Illuminate\Support\Str;
use function PHPUnit\Framework\stringContains;

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
            'itemId' => (string)$item->id,
            'description' => Str::limit($item?->itemable->name ?? 'Item', 50, ''),
            'price' => (float)number_format($item->sub_total, 2, '.', ''),
            'quantity' => 1,
        ])->toArray();

        $extraItems = [];

        if ($this->order->delivery_amount > 0) {
            $extraItems[] = [
                'itemId' => (string)Str::uuid(),
                'description' => 'Delivery Fee',
                'price' =>(float) number_format($this->order->delivery_amount, 2, '.', ''),
                'quantity' => 1,
            ];
        }
//        if (setting('tax') > 0) {
//            $taxAmount = getPriceAfterTax(setting('tax'), $this->order->subtotal);
//            $extraItems[] = [
//                'itemId' => (string)'1234',
//                'description' => 'Tax',
//                'price' => (float)number_format((float)$taxAmount, 2, '.', ''),
//                'quantity' => 1,
//            ];
//        }

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
        $merchantRef = str_replace('#', '', $this->order->order_number);

        $returnUrl =(string) config('services.fawry.redirection_url');
        $webhookUrl =(string) config('services.fawry.webhook_url');
        $profileId = ((string) $this->user?->id) ?? ((string)$this->guest?->id) ?? '0';

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
            'merchantRefNum' => 'mx-' . now()->format('YmdHis') . '-' . \Illuminate\Support\Str::random(6),
            'customerMobile' => '01000000000',
            'customerEmail' => $this->requestData->email,
            'customerName' => trim($this->requestData->first_name . ' ' . $this->requestData->last_name),
            'customerProfileId' => $profileId,
//            'paymentExpiry' => now()->addDays(2)->valueOf(),
            'language' => 'ar-eg',
            'chargeItems' => $allItems,
            'returnUrl' => $returnUrl,
            'paymentMethod' =>(string) $this->method,
            'authCaptureModePayment'=> false,
            'signature' => $signature,
            'orderWebHookUrl' => $webhookUrl,
        ];
    }
}
