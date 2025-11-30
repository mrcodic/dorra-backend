<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsMisrService implements SmsInterface
{
    private $conig;

    public function __construct()
    {
        $this->conig = config('services.sms_misr');
    }
    function normalizeEgyptianNumber($number): array|string|null
    {
        $number = preg_replace('/\D+/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = substr($number, 1);
        }
        if (!str_starts_with($number, '20')) {
            $number = '20' . $number;
        }
        return $number;
    }

    /**
     * @inheritDoc
     */
    public function send(array|string $numbers, string $message, array $options = [])
    {
        $numbers = array_map([$this, 'normalizeEgyptianNumber'], (array) $numbers);
        $numbers = implode(',', $numbers);
        $payload = [
            'environment' => $this->conig['environment'],
            'username' => $this->conig['username'],
            'password' => $this->conig['password'],
            'sender' => $this->conig['sender'],
            'mobile' => $numbers,
            'language' => $options['language'] ?? 3,
            'message' => $message,
        ];
        $response = Http::asForm()->post($this->conig['base_url'] . 'api/SMS', $payload);
        Log::info('SmsMisr', [$response->json(),$payload]);
        return $response->json();
    }
}
