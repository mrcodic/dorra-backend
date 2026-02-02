<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GenAiImageService
{
    public const PRIMARY_MODEL = 'gemini-3-pro-image-preview';

    public const MODEL_CHAIN = [
        self::PRIMARY_MODEL,
        'gemini-2.5-flash-image',
        'gemini-1.5-flash',
        'gemini-1.5-pro-latest',
    ];

    private int $perRequestCount = 1; // one image
    private int $chunk = 1;

    // limiter
    private int $limitPerMinute = 50;
    private string $limitKeyPrefix = 'genai:limiter:minute';

    // breaker
    private int $breakerTtlSec = 180;

    public function __construct(
        private readonly ?string $apiKey
    )
    {
    }

    public function generate(string $prompt, ?string $negativePrompt = null): array
    {
        if (config('app.ai_fake_mode')) {
            return $this->fakeGenerate($prompt, $negativePrompt);
        }
        $prompt = trim($prompt);
        $neg = trim((string)$negativePrompt);

        $instruction = $neg !== ''
            ? $prompt . "\n\nNegative prompt: " . $neg
            : $prompt;

        $images = [];
        $usedModel = null;
        $usageMetadata = null;
        $promptFeedback = null;

        foreach (self::MODEL_CHAIN as $model) {
            if ($this->breakerIsOpen($model)) continue;

            try {
                $batches = (int)ceil($this->perRequestCount / $this->chunk);

                for ($i = 0; $i < $batches; $i++) {
                    $want = min($this->chunk, $this->perRequestCount - count($images));

                    $ask = $want > 1
                        ? $instruction . "\n\nGenerate {$want} different logo variations in one response."
                        : $instruction;

                    $result = $this->generateOnce($model, $ask);

                    if (!empty($result['images'])) {
                        foreach ($result['images'] as $url) {
                            if (count($images) < $this->perRequestCount) $images[] = $url;
                        }

                        $usedModel = $model;
                        $usageMetadata = $result['usageMetadata'] ?? null;
                        $promptFeedback = $result['promptFeedback'] ?? null;
                    }

                    if (count($images) >= $this->perRequestCount) {
                        break 2; // stop model loop
                    }
                }
            } catch (\Throwable $e) {
                $status = (int)($e->getCode() ?: 0);
                if ($status >= 500) {
                    $this->tripBreaker($model);
                }
                continue;
            }
        }

        if (!count($images)) {
            return [
                'ok' => false,
                'status' => 503,
                'error' => 'All image models are currently unavailable or returned no image. Please try again shortly.',
            ];
        }

        $arabicNote = ($usedModel && $usedModel !== self::PRIMARY_MODEL)
            ? "تنبيه: قد لا تكون حروف اللغة العربية دقيقة في النتيجة لأن الموديل المستخدم ليس Gemini 3 Pro Image Preview. (Note: Arabic letters may be inaccurate because the model used is not Gemini 3 Pro Image Preview.)"
            : null;

        return [
            'ok' => true,
            'status' => 200,
            'images' => $images,
            'model' => $usedModel,
            'usage' => $usageMetadata,
            'promptFeedback' => $promptFeedback,
            'arabicNote' => $arabicNote,
        ];
    }

    /** ===== Circuit breaker ===== */
    private function breakerIsOpen(string $model): bool
    {
        return Cache::has($this->breakerKey($model));
    }

    private function breakerKey(string $model): string
    {
        return 'genai:breaker:' . Str::slug($model);
    }

    /** One provider call: limiter + backoff + timeout */
    private function generateOnce(string $model, string $instruction): array
    {

        $this->acquireLimiterTokenOrFail();

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $instruction],
                    ],
                ],
            ],
            'generationConfig' => [
                // Some models ignore this; harmless if unsupported
                'responseModalities' => ['IMAGE', 'TEXT'],
            ],
        ];

        $retries = 2;
        $baseMs = 900;
        $lastErr = null;

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $resp = Http::timeout(45)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url . '?key=' . urlencode($this->apiKey), $payload);

                if (in_array($resp->status(), [429, 503], true)) {
                    throw new \RuntimeException('Transient provider error', $resp->status());
                }

                if ($resp->failed()) {
                    throw new \RuntimeException(
                        $resp->json('error.message') ?: 'Provider request failed',
                        $resp->status()
                    );
                }

                $json = $resp->json();

                return [
                    'images' => $this->extractImages($json),
                    'usageMetadata' => $json['usageMetadata'] ?? null,
                    'promptFeedback' => $json['promptFeedback'] ?? null,
                ];
            } catch (\Throwable $e) {
                $lastErr = $e;

                $code = (int)($e->getCode() ?: 0);
                $isTransient = in_array($code, [429, 503], true);

                if (!$isTransient || $i === $retries) {
                    throw $e;
                }

                $delay = (int)($baseMs * (2 ** $i) + random_int(0, 250));
                usleep($delay * 1000);
            }
        }

        throw $lastErr ?: new \RuntimeException('Unknown error', 500);
    }

    /** ===== Limiter: simple per-minute reservoir ===== */
    private function acquireLimiterTokenOrFail(): void
    {
        $minuteKey = $this->limitKeyPrefix . ':' . now()->format('YmdHi');

        $count = Cache::increment($minuteKey);
        if ($count === 1) {
            Cache::put($minuteKey, 1, 70);
        }

        if ($count > $this->limitPerMinute) {
            throw new \RuntimeException('Rate limited (server limiter)', 429);
        }
    }

    /** Extract inlineData images -> data URLs */
    private function extractImages(array $json): array
    {
        $out = [];
        $candidates = $json['candidates'] ?? [];

        foreach ($candidates as $c) {
            $parts = data_get($c, 'content.parts', []);
            foreach ($parts as $p) {
                $b64 = data_get($p, 'inlineData.data');
                if ($b64) {
                    $mime = data_get($p, 'inlineData.mimeType', 'image/png');
                    $out[] = "data:{$mime};base64,{$b64}";
                }
            }
        }

        return $out;
    }

    private function tripBreaker(string $model): void
    {
        Cache::put($this->breakerKey($model), 1, $this->breakerTtlSec);
    }

    public function estimateTokens(string $prompt, ?string $negativePrompt = null, int $outputImages = 1, bool $hasInputImage = false): int
    {
        $text = trim($prompt) . "\n" . trim((string)$negativePrompt);

        // rough text estimate: ~1 token لكل 4 chars (تقريب)
        $textTokens = (int)ceil(mb_strlen($text) / 4);

        $inputImageTokens  = $hasInputImage ? 560 : 0;        // لو عندك input image
        $outputImageTokens = 1120 * $outputImages;            // 1024x1024

        // + margin buffer (اختياري) عشان التقدير مايبقاش أقل بزيادة
        $buffer = 100;

        return $textTokens + $inputImageTokens + $outputImageTokens + $buffer;
    }

    private function fakeGenerate(string $prompt, ?string $negativePrompt = null): array
    {
        // simulate real latency
        usleep(random_int(300, 900) * 1000);

        $promptLower = strtolower($prompt);

        // 🔥 FORCE FAILURE TEST
        if (str_contains($promptLower, 'fail')) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Simulated AI failure (fake mode)',
            ];
        }

        // simulate token usage variation
        $tokens = random_int(700, 2000);

        // fake base64 image (tiny pixel)
        $fakeImage = 'data:image/png;base64,' .
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';

        return [
            'ok' => true,
            'status' => 200,
            'images' => [$fakeImage],
            'model' => 'fake-gemini',
            'usage' => [
                'totalTokenCount' => $tokens
            ],
            'promptFeedback' => null,
            'arabicNote' => 'FAKE MODE ACTIVE',
        ];
    }


}
