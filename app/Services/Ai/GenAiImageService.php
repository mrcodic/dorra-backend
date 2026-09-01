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

    private const REMOVABLE_BG_INSTRUCTION = <<<'PROMPT'
Generate only the requested artwork, centered and isolated.

Place the artwork on one completely uniform solid chroma-green background using exactly #00FF00.

The entire area outside the artwork must use the same flat #00FF00 background.

Important:
- Do not generate transparency.
- Do not generate a transparency preview.
- Do not create any pattern behind the artwork.
- Do not create any texture behind the artwork.
- Do not create gradients on the background.
- Do not create lighting variations on the background.
- Do not create shadows on the background.
- Do not create a wall, floor, room, table, environment, or scene.
- Do not place the artwork on a product mockup.
- Do not create frames or borders around the image.

Do not use #00FF00 or chroma green inside the actual artwork.

Keep a clean visual separation between the artwork and the temporary solid background.

The #00FF00 background is temporary and will be removed programmatically after generation.
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
                $negativePrompt,
                $transparentBackground
            );
        }

        $prompt = trim($prompt);
        $negativePrompt = trim((string) $negativePrompt);

        $instruction = $prompt;

        if ($negativePrompt !== '') {
            $instruction .= "\n\nAvoid the following:\n" . $negativePrompt;
        }

        if ($transparentBackground) {
            $instruction .= "\n\n" . self::REMOVABLE_BG_INSTRUCTION;
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
                        ? $instruction . "\n\nGenerate {$want} different design variations in one response."
                        : $instruction;

                    $result = $this->generateOnce(
                        $model,
                        $ask
                    );

                    if (!empty($result['images'])) {
                        foreach ($result['images'] as $image) {
                            if ($transparentBackground) {
                                $image = $this->removeSolidGeneratedBackground(
                                    $image
                                );
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

        $arabicNote = (
            $usedModel &&
            $usedModel !== self::PRIMARY_MODEL
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
                        $url . '?key=' . urlencode($this->apiKey),
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
                    'images' => $this->extractImages($json),
                    'usageMetadata' => $json['usageMetadata'] ?? null,
                    'promptFeedback' => $json['promptFeedback'] ?? null,
                ];
            } catch (\Throwable $e) {
                $lastErr = $e;

                $code = (int) ($e->getCode() ?: 0);

                $isTransient = in_array(
                    $code,
                    [429, 503],
                    true
                );

                if (!$isTransient || $i === $retries) {
                    throw $e;
                }

                $delay = (int) (
                    $baseMs * (2 ** $i)
                    + random_int(0, 250)
                );

                usleep($delay * 1000);
            }
        }

        throw $lastErr
            ?: new \RuntimeException(
                'Unknown error',
                500
            );
    }

    private function extractImages(array $json): array
    {
        $out = [];

        $candidates = $json['candidates'] ?? [];

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

                $out[] = "data:{$mime};base64,{$b64}";
            }
        }

        return $out;
    }

    private function removeSolidGeneratedBackground(
        string $dataUrl
    ): string {
        if (!class_exists(\Imagick::class)) {
            return $dataUrl;
        }

        [, $binary] = $this->decodeDataUrl(
            $dataUrl
        );

        if (!$binary) {
            return $dataUrl;
        }

        try {
            $image = new \Imagick();

            $image->readImageBlob(
                $binary
            );

            if ($image->getNumberImages() > 1) {
                $image->setIteratorIndex(0);

                $first = clone $image->getImage();

                $image->clear();
                $image->destroy();

                $image = $first;
            }

            $image->setImageFormat('png');

            $image->setImageColorspace(
                \Imagick::COLORSPACE_SRGB
            );

            $image->setImageAlphaChannel(
                \Imagick::ALPHACHANNEL_SET
            );

            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            if (!$width || !$height) {
                $image->clear();
                $image->destroy();

                return $dataUrl;
            }

            /*
             * If Gemini actually returned real transparency,
             * don't touch it.
             */
            if ($this->hasRealTransparentBorder($image)) {
                $result = $this->encodeDataUrl(
                    'image/png',
                    $image->getImageBlob()
                );

                $image->clear();
                $image->destroy();

                return $result;
            }

            $transparent = new \ImagickPixel(
                'transparent'
            );

            $quantumRange =
                \Imagick::getQuantumRange()['quantumRangeLong']
                ?? 65535;

            /*
             * Gemini may slightly modify #00FF00,
             * so use a moderate fuzz.
             */
            $fuzz = 0.10 * $quantumRange;

            /*
             * Sample many actual border points.
             *
             * We don't assume Gemini returned exact #00FF00.
             *
             * floodFillPaintImage only removes areas connected
             * to each sampled border location.
             */
            $points = $this->getBorderSamplePoints(
                $width,
                $height
            );

            foreach ($points as [$x, $y]) {
                $pixel = $image->getImagePixelColor(
                    $x,
                    $y
                );

                $color = $pixel->getColor(true);

                /*
                 * Already transparent at this point?
                 */
                if (($color['a'] ?? 1) <= 0.03) {
                    continue;
                }

                /*
                 * Protect against accidentally starting
                 * flood-fill from a piece of artwork that
                 * happens to touch the outer edge.
                 *
                 * The requested background is strongly green.
                 */
                if (!$this->looksLikeChromaGreen($pixel)) {
                    continue;
                }

                $image->floodFillPaintImage(
                    $transparent,
                    $fuzz,
                    $pixel,
                    $x,
                    $y,
                    false
                );
            }

            /*
             * Second pass using slightly larger fuzz.
             *
             * Only from locations that are still green-ish
             * and still connected to the outside.
             *
             * This catches anti-aliased / AI-variant green.
             */
            $secondFuzz = 0.16 * $quantumRange;

            foreach ($points as [$x, $y]) {
                $pixel = $image->getImagePixelColor(
                    $x,
                    $y
                );

                $color = $pixel->getColor(true);

                if (($color['a'] ?? 1) <= 0.03) {
                    continue;
                }

                if (!$this->looksLikeChromaGreen($pixel)) {
                    continue;
                }

                $image->floodFillPaintImage(
                    $transparent,
                    $secondFuzz,
                    $pixel,
                    $x,
                    $y,
                    false
                );
            }

            /*
             * Remove a tiny green fringe only from pixels
             * immediately neighboring transparent background.
             */
            $this->cleanChromaEdge(
                $image
            );

            $image->setImageFormat('png');

            $result = $this->encodeDataUrl(
                'image/png',
                $image->getImageBlob()
            );

            $image->clear();
            $image->destroy();

            return $result;
        } catch (\Throwable $e) {
            report($e);

            return $dataUrl;
        }
    }

    private function getBorderSamplePoints(
        int $width,
        int $height
    ): array {
        $points = [];

        $steps = 30;

        for ($i = 0; $i <= $steps; $i++) {
            $x = (int) round(
                ($width - 1) * ($i / $steps)
            );

            $y = (int) round(
                ($height - 1) * ($i / $steps)
            );

            $points[] = [$x, 0];
            $points[] = [$x, $height - 1];

            $points[] = [0, $y];
            $points[] = [$width - 1, $y];
        }

        return $points;
    }

    private function looksLikeChromaGreen(
        \ImagickPixel $pixel
    ): bool {
        $color = $pixel->getColor();

        $r = (int) ($color['r'] ?? 0);
        $g = (int) ($color['g'] ?? 0);
        $b = (int) ($color['b'] ?? 0);

        /*
         * Deliberately broad enough for Gemini variations,
         * while still requiring green to dominate.
         */
        return (
            $g >= 120
            && $g >= ($r * 1.30)
            && $g >= ($b * 1.20)
            && ($g - $r) >= 45
            && ($g - $b) >= 30
        );
    }

    private function cleanChromaEdge(
        \Imagick $image
    ): void {
        try {
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();

            if (
                $width < 3
                || $height < 3
            ) {
                return;
            }

            /*
             * Clone alpha so we can identify pixels
             * immediately next to transparent areas.
             */
            $alpha = clone $image;

            $alpha->separateImageChannel(
                \Imagick::CHANNEL_ALPHA
            );

            try {
                $kernel = \ImagickKernel::fromBuiltIn(
                    \Imagick::KERNEL_DISK,
                    '1'
                );

                /*
                 * Slightly grow the transparent region
                 * toward the artwork.
                 */
                $alpha->morphology(
                    \Imagick::MORPHOLOGY_ERODE,
                    1,
                    $kernel
                );

                $alpha->gaussianBlurImage(
                    0.25,
                    0.15
                );

                $image->setImageAlphaChannel(
                    \Imagick::ALPHACHANNEL_OFF
                );

                $image->compositeImage(
                    $alpha,
                    \Imagick::COMPOSITE_COPYOPACITY,
                    0,
                    0
                );
            } catch (\Throwable $e) {
                /*
                 * Morphology isn't essential.
                 */
            }

            $alpha->clear();
            $alpha->destroy();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function hasRealTransparentBorder(
        \Imagick $image
    ): bool {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return false;
        }

        $samples = 0;
        $transparent = 0;

        foreach (
            $this->getBorderSamplePoints(
                $width,
                $height
            ) as [$x, $y]
        ) {
            $pixel = $image->getImagePixelColor(
                $x,
                $y
            );

            $color = $pixel->getColor(true);

            $alpha = (float) (
                $color['a']
                ?? 1
            );

            $samples++;

            if ($alpha <= 0.03) {
                $transparent++;
            }
        }

        if (!$samples) {
            return false;
        }

        return (
                $transparent / $samples
            ) >= 0.50;
    }

    private function decodeDataUrl(
        string $dataUrl
    ): array {
        if (
            !preg_match(
                '/^data:(.*?);base64,(.*)$/s',
                $dataUrl,
                $matches
            )
        ) {
            return [
                null,
                null,
            ];
        }

        $mime = $matches[1]
            ?? 'image/png';

        $binary = base64_decode(
            $matches[2] ?? '',
            true
        );

        return [
            $mime,
            $binary ?: null,
        ];
    }

    private function encodeDataUrl(
        string $mime,
        string $binary
    ): string {
        return 'data:'
            . $mime
            . ';base64,'
            . base64_encode(
                $binary
            );
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

    private function tripBreaker(
        string $model
    ): void {
        Cache::put(
            $this->breakerKey($model),
            1,
            $this->breakerTtlSec
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

    public function estimateTokens(
        string $prompt,
        ?string $negativePrompt = null,
        int $outputImages = 1,
        bool $hasInputImage = false
    ): int {
        $text =
            trim($prompt)
            . "\n"
            . trim(
                (string) $negativePrompt
            );

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
        ?string $negativePrompt = null,
        bool $transparentBackground = false
    ): array {
        usleep(
            random_int(
                300,
                900
            ) * 1000
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

        $mime = mime_content_type(
            $imagePath
        ) ?: 'image/png';

        $fakeImage =
            'data:'
            . $mime
            . ';base64,'
            . base64_encode(
                file_get_contents(
                    $imagePath
                )
            );

        if ($transparentBackground) {
            $fakeImage =
                $this->removeSolidGeneratedBackground(
                    $fakeImage
                );
        }

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
