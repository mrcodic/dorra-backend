<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GenAiImageService
{
    public const PRIMARY_MODEL = 'gemini-3-pro-image';

    public const MODEL_CHAIN = [
        self::PRIMARY_MODEL,
        'gemini-3.1-flash-image',
        'gemini-2.5-flash-image',
    ];

    private const TRANSPARENT_BG_INSTRUCTION = <<<'PROMPT'
Create only the isolated artwork.

The final output should be a PNG image with a transparent background.

Everything outside the actual artwork should be transparent.

Do not place the artwork on a product mockup.
Do not create a surrounding environment or scene.
Do not add a visible background surface.
Keep the artwork isolated and clean.

Preserve all colors that belong to the artwork itself, including white, cream, light gray, highlights, internal holes, fine lines, and decorative details.
PROMPT;

    private int $perRequestCount = 1;
    private int $chunk = 1;

    private int $limitPerMinute = 50;
    private string $limitKeyPrefix = 'genai:limiter:minute';

    private int $breakerTtlSec = 180;

    public function __construct(
        private readonly ?string $apiKey
    ) {
    }

    public function generate(
        string $prompt,
        ?string $negativePrompt = null,
        bool $transparentBackground = false
    ): array {
        if (config('app.ai_fake_mode')) {
            return $this->fakeGenerate($prompt, $negativePrompt);
        }

        $prompt = trim($prompt);
        $neg = trim((string) $negativePrompt);

        $instruction = $prompt;

        if ($neg !== '') {
            $instruction .= "\n\nAvoid the following:\n" . $neg;
        }

        if ($transparentBackground) {
            $instruction .= "\n\n" . self::TRANSPARENT_BG_INSTRUCTION;
        }

        $images = [];
        $usedModel = null;
        $usageMetadata = null;
        $promptFeedback = null;

        foreach (self::MODEL_CHAIN as $model) {
            if ($this->breakerIsOpen($model)) {
                continue;
            }

            try {
                $batches = (int) ceil($this->perRequestCount / $this->chunk);

                for ($i = 0; $i < $batches; $i++) {
                    $want = min(
                        $this->chunk,
                        $this->perRequestCount - count($images)
                    );

                    $ask = $want > 1
                        ? $instruction . "\n\nGenerate {$want} different design variations in one response."
                        : $instruction;

                    $result = $this->generateOnce($model, $ask);

                    if (!empty($result['images'])) {
                        foreach ($result['images'] as $image) {
                            if ($transparentBackground) {
                                $image = $this->postProcessTransparentImage($image);
                            }

                            if (count($images) < $this->perRequestCount) {
                                $images[] = $image;
                            }
                        }

                        $usedModel = $model;
                        $usageMetadata = $result['usageMetadata'] ?? null;
                        $promptFeedback = $result['promptFeedback'] ?? null;
                    }

                    if (count($images) >= $this->perRequestCount) {
                        break 2;
                    }
                }
            } catch (\Throwable $e) {
                $status = (int) ($e->getCode() ?: 0);

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
            ? 'تنبيه: قد لا تكون حروف اللغة العربية دقيقة في النتيجة لأن الموديل المستخدم ليس Gemini 3 Pro Image Preview.'
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

    private function breakerIsOpen(string $model): bool
    {
        return Cache::has($this->breakerKey($model));
    }

    private function breakerKey(string $model): string
    {
        return 'genai:breaker:' . Str::slug($model);
    }

    private function generateOnce(string $model, string $instruction): array
    {
        $this->acquireLimiterTokenOrFail();

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $instruction,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['IMAGE', 'TEXT'],
            ],
        ];

        $retries = 2;
        $baseMs = 900;
        $lastErr = null;

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $resp = Http::timeout(45)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
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

                $code = (int) ($e->getCode() ?: 0);
                $isTransient = in_array($code, [429, 503], true);

                if (!$isTransient || $i === $retries) {
                    throw $e;
                }

                $delay = (int) ($baseMs * (2 ** $i) + random_int(0, 250));
                usleep($delay * 1000);
            }
        }

        throw $lastErr ?: new \RuntimeException('Unknown error', 500);
    }

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

    private function extractImages(array $json): array
    {
        $out = [];
        $candidates = $json['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $parts = data_get($candidate, 'content.parts', []);

            foreach ($parts as $part) {
                $b64 = data_get($part, 'inlineData.data');

                if (!$b64) {
                    continue;
                }

                $mime = data_get($part, 'inlineData.mimeType', 'image/png');
                $out[] = "data:{$mime};base64,{$b64}";
            }
        }

        return $out;
    }

    private function postProcessTransparentImage(string $dataUrl): string
    {
        if (!class_exists(\Imagick::class)) {
            return $dataUrl;
        }

        [$mime, $binary] = $this->decodeDataUrl($dataUrl);

        if (!$binary) {
            return $dataUrl;
        }

        try {
            $image = new \Imagick();
            $image->readImageBlob($binary);
            $image->setImageFormat('png');
            $image->setImageColorspace(\Imagick::COLORSPACE_SRGB);
            $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

            if ($this->hasRealTransparentBorder($image)) {
                $result = $this->encodeDataUrl(
                    'image/png',
                    $image->getImagesBlob()
                );

                $image->clear();
                $image->destroy();

                return $result;
            }

            $this->removeLikelyGeneratedBackground($image);

            $result = $this->encodeDataUrl(
                'image/png',
                $image->getImagesBlob()
            );

            $image->clear();
            $image->destroy();

            return $result;
        } catch (\Throwable $e) {
            report($e);
            return $dataUrl;
        }
    }

    private function hasRealTransparentBorder(\Imagick $image): bool
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return false;
        }

        $points = [
            [0, 0],
            [$width - 1, 0],
            [0, $height - 1],
            [$width - 1, $height - 1],
            [intdiv($width, 2), 0],
            [intdiv($width, 2), $height - 1],
            [0, intdiv($height, 2)],
            [$width - 1, intdiv($height, 2)],
        ];

        $transparentCount = 0;

        foreach ($points as [$x, $y]) {
            $pixel = $image->getImagePixelColor($x, $y);
            $color = $pixel->getColor(true);
            $alpha = $color['a'] ?? 1.0;

            if ($alpha <= 0.02) {
                $transparentCount++;
            }
        }

        return $transparentCount >= 4;
    }

    private function removeLikelyGeneratedBackground(\Imagick $image): void
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return;
        }

        $image->setImageFormat('png');
        $image->setImageColorspace(\Imagick::COLORSPACE_SRGB);
        $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

        /*
         * Detect the most common colors around the border.
         *
         * Fake transparency generated by AI normally has 2-4 repeating
         * background colors around all image edges.
         */
        $backgroundColors = $this->detectBorderBackgroundColors(
            $image,
            maxColors: 6
        );

        if (!$backgroundColors) {
            return;
        }

        $quantumRange = \Imagick::getQuantumRange()['quantumRangeLong'] ?? 65535;

        /*
         * Start relatively conservative.
         *
         * We only remove colors strongly matching the detected
         * generated background colors.
         */
        $fuzz = 0.10 * $quantumRange;

        foreach ($backgroundColors as $color) {
            $image->transparentPaintImage(
                $color,
                0,
                $fuzz,
                false
            );
        }

        /*
         * Second lighter pass catches compression / antialias pixels
         * around fake checkerboard cells.
         */
        $softFuzz = 0.15 * $quantumRange;

        foreach ($backgroundColors as $color) {
            $image->transparentPaintImage(
                $color,
                0,
                $softFuzz,
                false
            );
        }

        /*
         * Clean tiny leftover background fragments.
         */
        $this->cleanTransparentAlpha($image);

        $image->setImageFormat('png');
    }
    private function backgroundSamplePoints(int $width, int $height): array
    {
        $points = [];
        $steps = 8;

        for ($i = 0; $i <= $steps; $i++) {
            $x = (int) round(($width - 1) * ($i / $steps));
            $y = (int) round(($height - 1) * ($i / $steps));

            $points[] = [$x, 0];
            $points[] = [$x, $height - 1];
            $points[] = [0, $y];
            $points[] = [$width - 1, $y];
        }

        $points[] = [0, 0];
        $points[] = [$width - 1, 0];
        $points[] = [0, $height - 1];
        $points[] = [$width - 1, $height - 1];

        return $points;
    }

    private function decodeDataUrl(string $dataUrl): array
    {
        if (!preg_match('/^data:(.*?);base64,(.*)$/', $dataUrl, $matches)) {
            return [null, null];
        }

        $mime = $matches[1] ?? 'image/png';
        $binary = base64_decode($matches[2] ?? '', true);

        return [$mime, $binary ?: null];
    }

    private function encodeDataUrl(string $mime, string $binary): string
    {
        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    }

    private function tripBreaker(string $model): void
    {
        Cache::put($this->breakerKey($model), 1, $this->breakerTtlSec);
    }

    public function estimateTokens(
        string $prompt,
        ?string $negativePrompt = null,
        int $outputImages = 1,
        bool $hasInputImage = false
    ): int {
        $text = trim($prompt) . "\n" . trim((string) $negativePrompt);

        $textTokens = (int) ceil(mb_strlen($text) / 4);
        $inputImageTokens = $hasInputImage ? 560 : 0;
        $outputImageTokens = 1120 * $outputImages;
        $buffer = 100;

        return $textTokens + $inputImageTokens + $outputImageTokens + $buffer;
    }

    private function fakeGenerate(string $prompt, ?string $negativePrompt = null): array
    {
        usleep(random_int(300, 900) * 1000);

        $promptLower = strtolower($prompt);

        if (str_contains($promptLower, 'fail')) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Simulated AI failure (fake mode)',
            ];
        }

        $tokens = random_int(700, 2000);

        $imagePath = public_path('images/test/ai-image.png');

        if (!file_exists($imagePath)) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Fake AI test image not found.',
            ];
        }

        $mime = mime_content_type($imagePath) ?: 'image/png';

        $fakeImage = 'data:' . $mime . ';base64,' . base64_encode(
                file_get_contents($imagePath)
            );

        return [
            'ok' => true,
            'status' => 200,
            'images' => [$fakeImage],
            'model' => 'fake-gemini',
            'usage' => [
                'totalTokenCount' => $tokens,
            ],
            'promptFeedback' => null,
            'arabicNote' => 'FAKE MODE ACTIVE',
        ];
    }
}
