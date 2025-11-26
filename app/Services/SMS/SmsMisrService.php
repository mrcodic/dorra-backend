<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Http;

class SmsMisrService implements SmsInterface
{
    private $conig;

    public function __construct()
    {
        $this->conig = config('services.sms_misr');
    }

    /**
     * @inheritDoc
     */
    public function send(array|string $numbers, string $message, array $options = [])
    {
        $numbers = is_array($numbers) ? implode(',', $numbers) : $numbers;
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
        return $response->json();
    }
}
