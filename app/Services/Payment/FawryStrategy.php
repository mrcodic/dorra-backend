<?php

namespace App\Services\Payment;

use AllowDynamicProperties;
use App\Enums\Payment\StatusEnum;
use App\Models\Transaction;
use App\Repositories\Interfaces\PaymentGatewayRepositoryInterface;
use Illuminate\Support\Facades\Http;

#[AllowDynamicProperties] class FawryStrategy implements PaymentGatewayStrategy
{

    public function __construct(public PaymentGatewayRepositoryInterface $gatewayRepository, public $gatewayCode)
    {
        $this->baseUrl = config('services.fawry.base_url');
        $this->callback = config('services.fawry.redirection_url');
        $gateway = $this->gatewayRepository->query()
            ->whereCode($this->gatewayCode)
            ->active()
            ->first();

        if (!$gateway) {
            throw new \Exception("Payment gateway [{$this->gatewayCode}] not found or inactive.");
        }

        $this->config = config('services.fawry');
    }

    public function pay(array $payload, ?array $data): false|array
    {
        $url          = rtrim($this->baseUrl, '/') . '/fawrypay-api/api/payments/init';
        $merchantCode = (string) $this->config['merchant_code'];
        $secureKey    = (string) $this->config['secret_key'];

        $forceStatic  = (bool) ($this->config['force_static_payload'] ?? false);

        if ($forceStatic) {
            // ---- STATIC, KNOWN-GOOD PAYLOAD (ASCII only, short fields) ----
            $merchantRefNum    = 'mx-' . now()->format('YmdHis') . '-' . \Illuminate\Support\Str::random(6);
            $customerProfileId = '41';
            $returnUrl         = (string) $this->config['test_return_url'];
            $itemId            = '123';
            $qty               = 1;
            $price             = number_format(185.00, 2, '.', ''); // "185.00"

            // Signature: merchantCode + merchantRefNum + customerProfileId + returnUrl + itemId + qty + price + secureKey
            $rawSig    = $merchantCode . $merchantRefNum . $customerProfileId . $returnUrl . $itemId . $qty . $price . $secureKey;
            $signature = hash('sha256', $rawSig);

            $payload = [
                'merchantCode'           => $merchantCode,
                'merchantRefNum'         => $merchantRefNum,
                'customerMobile'         => '01000000000',
                'customerEmail'          => 'test@example.com',
                'customerName'           => 'Staging Test',
                'customerProfileId'      => $customerProfileId,
                'language'               => 'ar-eg',
                'chargeItems'            => [[
                    'itemId'      => $itemId,
                    'description' => 'Test Staging Item',
                    'price'       => (float) $price,
                    'quantity'    => $qty,
                ]],
                'returnUrl'              => $returnUrl,
                'paymentMethod'          => 'MWALLET',
                'authCaptureModePayment' => false,
                'signature'              => $signature,
                'orderWebHookUrl'        => (string) ($this->config['webhook_url'] ?? ''),
            ];
        }

        // ---------- PURE cURL CALL ----------
        $ch = curl_init();

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_HTTPHEADER     => [
                'User-Agent: curl/7.61.1',
                'Accept: */*',
                'Content-Type: application/json; charset=UTF-8',
                'Expect:',          // disable "Expect: 100-continue"
                'Connection: close',
            ],
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSLVERSION     => CURL_SSLVERSION_TLSv1_2,
            // optional timeouts
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $body   = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ct     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';

        curl_close($ch);

        // cURL-level error (DNS, SSL, timeout, etc.)
        if ($errno) {
            \Log::error('Fawry init cURL error', [
                'errno'  => $errno,
                'error'  => $error,
                'url'    => $url,
                'body'   => $body,
                'status' => $status,
            ]);
            return false;
        }

        $body = trim((string) $body);

        // HTTP failure (non 2xx)
        if ($status < 200 || $status >= 300) {
            \Log::error('Fawry init failed', [
                'status' => $status,
                'ct'     => $ct,
                'body'   => mb_substr($body, 0, 500),
            ]);
            return false;
        }

        // WAF HTML page?
        if (stripos($ct, 'text/html') !== false && str_contains($body, 'Request Rejected')) {
            if (preg_match('/support ID is:\s*([0-9]+)/i', $body, $m)) {
                \Log::warning('Fawry WAF blocked', ['supportId' => $m[1]]);
            } else {
                \Log::warning('Fawry WAF blocked (no support id)', ['preview' => mb_substr($body, 0, 200)]);
            }
            return false;
        }

        // Normal success: text/plain with URL, or JSON with paymentLink/paymentUrl/redirectUrl/url
        $checkoutUrl = null;

        if (stripos($ct, 'text/plain') !== false && filter_var($body, FILTER_VALIDATE_URL)) {
            $checkoutUrl = $body;
        } else {
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $checkoutUrl = $json['paymentLink']
                    ?? $json['paymentUrl']
                    ?? $json['redirectUrl']
                    ?? $json['url']
                    ?? null;
            }
        }

        if (!$checkoutUrl) {
            \Log::warning('Fawry init unknown response', [
                'status' => $status,
                'ct'     => $ct,
                'body'   => mb_substr($body, 0, 500),
            ]);
            return false;
        }

        $amount = collect($payload['chargeItems'] ?? [])->sum(
            fn ($i) => (float) $i['price'] * (int) $i['quantity']
        );

        return [
            'order_id'     => $payload['merchantRefNum'] ?? null,
            'amount'       => $amount,
            'checkout_url' => $checkoutUrl,
        ];
    }
    public function storeTransaction($orderData, $data, $paymentMethod): array
    {
        $transaction = Transaction::create([
            'order_id' => $data['order']->id,
            'amount' => $orderData['amount'],
            'payment_method' => $paymentMethod,
            'payment_status' => StatusEnum::PENDING,
            'transaction_id' => $orderData['order_id'],
            'response_message' => json_encode($orderData),
            'success_url' => request()->success_url ?? $this->callback,
            'failure_url' => request()->failure_url ?? $this->callback,
            'pending_url' => request()->pending_url ?? $this->callback,
            'expiration_date' => now()->addDays(2),
        ]);

        return [
            'transaction_id' => $transaction->transaction_id,
            'amount' => $orderData['amount'],
            'payment_url' => $orderData['checkout_url'],
        ];
    }


    public function refund(string $transactionId): bool
    {
        return true;
    }
}
