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

The final output must be a PNG image with a real alpha channel.

Everything outside the actual artwork must contain no visible pixels and must be fully transparent with alpha equal to zero.

The output must contain only the requested design elements.

Do not create any visible surrounding surface, backdrop, canvas, environment, frame, border, texture, scenery, decorative area, or background element.

Do not place the artwork on a product or mockup.

Do not add shadows, lighting effects, glow, reflections, or visual elements outside the actual artwork.

Preserve every color that belongs to the artwork itself, including white, cream, light gray, highlights, internal holes, text details, thin lines, borders, and small decorative elements.

Keep clean edges around the artwork.

Return the generated artwork as an isolated production-ready PNG asset with real transparency, ready to be placed directly inside a design editor or on a product.
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
            return $this->fakeGenerate(
                $prompt,
                $negativePrompt
            );
        }

        $prompt = trim($prompt);
        $neg = trim((string) $negativePrompt);

        $instruction = $prompt;

        if ($neg !== '') {
            $instruction .= "\n\nAvoid the following:\n" . $neg;
        }

        if ($transparentBackground) {
            $instruction .= "\n\n"
                . self::TRANSPARENT_BG_INSTRUCTION;
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
                $batches = (int) ceil(
                    $this->perRequestCount / $this->chunk
                );

                for ($i = 0; $i < $batches; $i++) {
                    $want = min(
                        $this->chunk,
                        $this->perRequestCount - count($images)
                    );

                    $ask = $want > 1
                        ? $instruction
                        . "\n\nGenerate {$want} different design variations in one response."
                        : $instruction;

                    $result = $this->generateOnce(
                        $model,
                        $ask
                    );

                    if (!empty($result['images'])) {
                        foreach ($result['images'] as $image) {
                            if (
                                count($images)
                                < $this->perRequestCount
                            ) {
                                $images[] = $image;
                            }
                        }

                        $usedModel = $model;

                        $usageMetadata =
                            $result['usageMetadata'] ?? null;

                        $promptFeedback =
                            $result['promptFeedback'] ?? null;
                    }

                    if (
                        count($images)
                        >= $this->perRequestCount
                    ) {
                        break 2;
                    }
                }
            } catch (\Throwable $e) {
                $status = (int) (
                $e->getCode() ?: 0
                );

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

        $arabicNote = (
            $usedModel
            && $usedModel !== self::PRIMARY_MODEL
        )
            ? 'تنبيه: قد لا تكون حروف اللغة العربية دقيقة في النتيجة لأن الموديل المستخدم ليس Gemini 3 Pro Image Preview. (Note: Arabic letters may be inaccurate because the model used is not Gemini 3 Pro Image Preview.)'
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

    private function breakerIsOpen(
        string $model
    ): bool {
        return Cache::has(
            $this->breakerKey($model)
        );
    }

    private function breakerKey(
        string $model
    ): string {
        return 'genai:breaker:'
            . Str::slug($model);
    }

    private function generateOnce(
        string $model,
        string $instruction
    ): array {
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
                'responseModalities' => [
                    'IMAGE',
                    'TEXT',
                ],
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
                    ->post(
                        $url
                        . '?key='
                        . urlencode($this->apiKey),
                        $payload
                    );

                if (
                    in_array(
                        $resp->status(),
                        [429, 503],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Transient provider error',
                        $resp->status()
                    );
                }

                if ($resp->failed()) {
                    throw new \RuntimeException(
                        $resp->json('error.message')
                            ?: 'Provider request failed',
                        $resp->status()
                    );
                }

                $json = $resp->json();

                return [
                    'images' => $this->extractImages(
                        $json
                    ),

                    'usageMetadata' =>
                        $json['usageMetadata']
                        ?? null,

                    'promptFeedback' =>
                        $json['promptFeedback']
                        ?? null,
                ];
            } catch (\Throwable $e) {
                $lastErr = $e;

                $code = (int) (
                $e->getCode() ?: 0
                );

                $isTransient = in_array(
                    $code,
                    [429, 503],
                    true
                );

                if (
                    !$isTransient
                    || $i === $retries
                ) {
                    throw $e;
                }

                $delay = (int) (
                    $baseMs * (2 ** $i)
                    + random_int(0, 250)
                );

                usleep(
                    $delay * 1000
                );
            }
        }

        throw $lastErr
            ?: new \RuntimeException(
                'Unknown error',
                500
            );
    }

    private function acquireLimiterTokenOrFail(): void
    {
        $minuteKey =
            $this->limitKeyPrefix
            . ':'
            . now()->format('YmdHi');

        $count = Cache::increment(
            $minuteKey
        );

        if ($count === 1) {
            Cache::put(
                $minuteKey,
                1,
                70
            );
        }

        if (
            $count
            > $this->limitPerMinute
        ) {
            throw new \RuntimeException(
                'Rate limited (server limiter)',
                429
            );
        }
    }

    private function extractImages(
        array $json
    ): array {
        $out = [];

        $candidates =
            $json['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $parts = data_get(
                $candidate,
                'content.parts',
                []
            );

            foreach ($parts as $part) {
                $b64 = data_get(
                    $part,
                    'inlineData.data'
                );

                if (!$b64) {
                    continue;
                }

                $mime = data_get(
                    $part,
                    'inlineData.mimeType',
                    'image/png'
                );

                $out[] =
                    "data:{$mime};base64,{$b64}";
            }
        }

        return $out;
    }

    private function tripBreaker(
        string $model
    ): void {
        Cache::put(
            $this->breakerKey($model),
            1,
            $this->breakerTtlSec
        );
    }

    public function estimateTokens(
        string $prompt,
        ?string $negativePrompt = null,
        int $outputImages = 1,
        bool $hasInputImage = false
    ): int {
        $text =
            trim($prompt)
            . "\n"
            . trim((string) $negativePrompt);

        $textTokens = (int) ceil(
            mb_strlen($text) / 4
        );

        $inputImageTokens =
            $hasInputImage
                ? 560
                : 0;

        $outputImageTokens =
            1120 * $outputImages;

        $buffer = 100;

        return
            $textTokens
            + $inputImageTokens
            + $outputImageTokens
            + $buffer;
    }

    private function fakeGenerate(
        string $prompt,
        ?string $negativePrompt = null
    ): array {
        usleep(
            random_int(300, 900)
            * 1000
        );

        $promptLower = strtolower(
            $prompt
        );

        if (
            str_contains(
                $promptLower,
                'fail'
            )
        ) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Simulated AI failure (fake mode)',
            ];
        }

        $tokens = random_int(
            700,
            2000
        );

        $imagePath = public_path(
            'images/test/ai-image.png'
        );

        if (!file_exists($imagePath)) {
            return [
                'ok' => false,
                'status' => 500,
                'error' => 'Fake AI test image not found.',
            ];
        }

        $mime =
            mime_content_type($imagePath)
                ?: 'image/png';

        $fakeImage =
            'data:'
            . $mime
            . ';base64,'
            . base64_encode(
                file_get_contents($imagePath)
            );

        return [
            'ok' => true,
            'status' => 200,

            'images' => [
                $fakeImage,
            ],

            'model' => 'fake-gemini',

            'usage' => [
                'totalTokenCount' => $tokens,
            ],

            'promptFeedback' => null,

            'arabicNote' =>
                'FAKE MODE ACTIVE',
        ];
    }
}
