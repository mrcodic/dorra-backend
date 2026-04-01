<?php

namespace App\Services\Mockup;

use RuntimeException;

/**
 * Thin Laravel client for the Python mockup microservice (mockup_service.py).
 *
 * The Python service runs locally on 127.0.0.1:5050.
 * It accepts POST /render with JSON, returns PNG bytes.
 *
 * Drop-in for the old Imagick renderer — same $cfg array, same route code.
 * Only change in the route: use $img->getImageBlob() → just echo renderToPng().
 */
class KittlSeatBucketRenderer
{
    private string $serviceUrl;

    public function __construct(string $serviceUrl = 'http://127.0.0.1:5050')
    {
        $this->serviceUrl = rtrim($serviceUrl, '/');
    }

    /**
     * Render mockup. Returns raw PNG bytes.
     * Usage: return response($renderer->renderToPng($cfg), 200, ['Content-Type' => 'image/png']);
     */
    public function renderToPng(array $cfg): string
    {
        return $this->post('/render', $cfg);
    }

    /**
     * Alias kept for route compatibility.
     */
    public function render(array $cfg): string
    {
        return $this->renderToPng($cfg);
    }

    public function isHealthy(): bool
    {
        try {
            $ch = curl_init($this->serviceUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $r = curl_exec($ch);
            curl_close($ch);
            return trim($r) === 'ok';
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function post(string $path, array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $ch   = curl_init($this->serviceUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) throw new RuntimeException("Mockup service unreachable: $err");
        if ($code !== 200) throw new RuntimeException("Mockup service error $code: " . substr($response, 0, 300));

        return $response;
    }
}