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

The final output should be a PNG image with a genuinely transparent background.

Everything outside the actual artwork should contain no visible pixels.

Do not place the artwork on a product mockup.
Do not create a surrounding environment, scene, backdrop, canvas, frame, texture, or decorative background.

Keep the artwork isolated and production-ready.

Preserve every color and detail that belongs to the artwork itself, including white, cream, light gray, black, highlights, fine lines, borders, internal details, decorative elements, and typography.

Return only the isolated artwork ready to be placed directly inside a design editor or onto another product.
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
                                $image = $this->postProcessTransparentImage(
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

    private function postProcessTransparentImage(
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

            /*
             * If animated/multi-frame somehow comes back,
             * work with the first image.
             */
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

            /*
             * If the border already contains significant
             * true transparency, leave the image untouched.
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

            /*
             * Gemini painted fake transparency/background.
             */
            $this->removeLikelyGeneratedBackground(
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

    private function hasRealTransparentBorder(
        \Imagick $image
    ): bool {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return false;
        }

        $transparent = 0;
        $samples = 0;
        $steps = 50;

        for ($i = 0; $i <= $steps; $i++) {
            $x = (int) round(
                ($width - 1) * ($i / $steps)
            );

            $y = (int) round(
                ($height - 1) * ($i / $steps)
            );

            $points = [
                [$x, 0],
                [$x, $height - 1],
                [0, $y],
                [$width - 1, $y],
            ];

            foreach ($points as [$px, $py]) {
                $pixel = $image->getImagePixelColor(
                    $px,
                    $py
                );

                $color = $pixel->getColor(true);

                $alpha = (float) (
                    $color['a'] ?? 1
                );

                $samples++;

                if ($alpha <= 0.03) {
                    $transparent++;
                }
            }
        }

        if (!$samples) {
            return false;
        }

        return (
                $transparent / $samples
            ) >= 0.50;
    }

    private function removeLikelyGeneratedBackground(
        \Imagick $image
    ): void {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return;
        }

        $image->setImageFormat('png');

        $image->setImageColorspace(
            \Imagick::COLORSPACE_SRGB
        );

        $image->setImageAlphaChannel(
            \Imagick::ALPHACHANNEL_SET
        );

        /*
         * Detect common colors around the border.
         *
         * Gemini's fake transparency generally repeats
         * those colors across the full outside background.
         */
        $palette = $this->detectBorderPalette(
            $image,
            12
        );

        if (!$palette) {
            return;
        }

        /*
         * Candidate mask:
         *
         * White = pixel resembles border background.
         * Black = pixel probably belongs to artwork.
         */
        $candidateMask =
            $this->buildBackgroundCandidateMask(
                $image,
                $palette,
                48
            );

        /*
         * Join tiny gaps in checkerboard / anti-aliasing.
         */
        try {
            $kernel = \ImagickKernel::fromBuiltIn(
                \Imagick::KERNEL_DISK,
                '1'
            );

            $candidateMask->morphology(
                \Imagick::MORPHOLOGY_CLOSE,
                1,
                $kernel
            );
        } catch (\Throwable $e) {
            // Optional cleanup only.
        }

        /*
         * Clone candidate mask and remove all white
         * areas that are connected to image borders.
         *
         * What remains white is enclosed background-like
         * color inside the artwork and must NOT be removed.
         */
        $remainingMask = clone $candidateMask;

        $this->removeBorderConnectedWhiteRegions(
            $remainingMask
        );

        /*
         * Build actual OUTER background mask.
         */
        $backgroundMask =
            $this->buildConnectedBackgroundMask(
                $candidateMask,
                $remainingMask
            );

        /*
         * Grow outer background by 1px.
         *
         * Removes the thin dark/gray seams Gemini leaves
         * around checkerboard cells and cut edges.
         */
        try {
            $kernel = \ImagickKernel::fromBuiltIn(
                \Imagick::KERNEL_DISK,
                '1'
            );

            $backgroundMask->morphology(
                \Imagick::MORPHOLOGY_DILATE,
                1,
                $kernel
            );
        } catch (\Throwable $e) {
            // Optional.
        }

        /*
         * backgroundMask:
         *
         * white = background/remove
         * black = artwork/keep
         *
         * Alpha needs the opposite:
         *
         * black = transparent
         * white = opaque
         */
        $alphaMask = clone $backgroundMask;

        $alphaMask->negateImage(
            false
        );

        /*
         * Very slight smoothing around cut edges.
         */
        try {
            $alphaMask->gaussianBlurImage(
                0.30,
                0.15
            );
        } catch (\Throwable $e) {
            // Optional.
        }

        /*
         * Replace alpha rather than modifying RGB colors.
         */
        $image->setImageAlphaChannel(
            \Imagick::ALPHACHANNEL_OFF
        );

        $image->compositeImage(
            $alphaMask,
            \Imagick::COMPOSITE_COPYOPACITY,
            0,
            0
        );

        $image->setImageFormat('png');

        $candidateMask->clear();
        $candidateMask->destroy();

        $remainingMask->clear();
        $remainingMask->destroy();

        $backgroundMask->clear();
        $backgroundMask->destroy();

        $alphaMask->clear();
        $alphaMask->destroy();
    }

    private function detectBorderPalette(
        \Imagick $image,
        int $maxColors = 12
    ): array {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if (!$width || !$height) {
            return [];
        }

        $clusters = [];
        $steps = 120;

        for ($i = 0; $i <= $steps; $i++) {
            $x = (int) round(
                ($width - 1) * ($i / $steps)
            );

            $y = (int) round(
                ($height - 1) * ($i / $steps)
            );

            $points = [
                [$x, 0],
                [$x, $height - 1],
                [0, $y],
                [$width - 1, $y],
            ];

            foreach ($points as [$px, $py]) {
                $pixel =
                    $image->getImagePixelColor(
                        $px,
                        $py
                    );

                $rgb = $pixel->getColor();

                /*
                 * Quantize colors so slight AI variations
                 * are grouped into the same background family.
                 */
                $r = (int) round(
                        ($rgb['r'] ?? 0) / 12
                    ) * 12;

                $g = (int) round(
                        ($rgb['g'] ?? 0) / 12
                    ) * 12;

                $b = (int) round(
                        ($rgb['b'] ?? 0) / 12
                    ) * 12;

                $r = min(
                    255,
                    max(0, $r)
                );

                $g = min(
                    255,
                    max(0, $g)
                );

                $b = min(
                    255,
                    max(0, $b)
                );

                $key = "{$r}:{$g}:{$b}";

                if (!isset($clusters[$key])) {
                    $clusters[$key] = [
                        'count' => 0,
                        'r' => $r,
                        'g' => $g,
                        'b' => $b,
                    ];
                }

                $clusters[$key]['count']++;
            }
        }

        uasort(
            $clusters,
            fn($a, $b) =>
                $b['count']
                <=>
                $a['count']
        );

        $result = [];

        foreach ($clusters as $cluster) {
            if ($cluster['count'] < 3) {
                continue;
            }

            $result[] = [
                'r' => $cluster['r'],
                'g' => $cluster['g'],
                'b' => $cluster['b'],
            ];

            if (
                count($result)
                >= $maxColors
            ) {
                break;
            }
        }

        return $result;
    }

    private function buildBackgroundCandidateMask(
        \Imagick $image,
        array $palette,
        int $distanceThreshold = 48
    ): \Imagick {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        $mask = new \Imagick();

        $mask->newImage(
            $width,
            $height,
            new \ImagickPixel('black'),
            'png'
        );

        $mask->setImageColorspace(
            \Imagick::COLORSPACE_GRAY
        );

        $thresholdSquared =
            $distanceThreshold
            * $distanceThreshold;

        /*
         * Using direct loops here is slower than a pure
         * Imagick operation, but much safer for our specific
         * multi-color checkerboard case.
         */
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $pixel =
                    $image->getImagePixelColor(
                        $x,
                        $y
                    );

                $rgb = $pixel->getColor();

                $r = (int) ($rgb['r'] ?? 0);
                $g = (int) ($rgb['g'] ?? 0);
                $b = (int) ($rgb['b'] ?? 0);

                $isBackground = false;

                foreach ($palette as $target) {
                    $dr =
                        $r
                        - $target['r'];

                    $dg =
                        $g
                        - $target['g'];

                    $db =
                        $b
                        - $target['b'];

                    $distanceSquared =
                        ($dr * $dr)
                        + ($dg * $dg)
                        + ($db * $db);

                    if (
                        $distanceSquared
                        <= $thresholdSquared
                    ) {
                        $isBackground = true;

                        break;
                    }
                }

                $mask
                    ->getImagePixelColor($x, $y)
                    ->setColor(
                        $isBackground
                            ? 'white'
                            : 'black'
                    );
            }

            /*
             * Force pixel cache synchronization.
             */
            $mask->syncImage();
        }

        return $mask;
    }

    private function removeBorderConnectedWhiteRegions(
        \Imagick $mask
    ): void {
        $width = $mask->getImageWidth();
        $height = $mask->getImageHeight();

        if (!$width || !$height) {
            return;
        }

        $black =
            new \ImagickPixel(
                'black'
            );

        $white =
            new \ImagickPixel(
                'white'
            );

        $stepX = max(
            1,
            (int) floor(
                $width / 100
            )
        );

        $stepY = max(
            1,
            (int) floor(
                $height / 100
            )
        );

        for (
            $x = 0;
            $x < $width;
            $x += $stepX
        ) {
            $this->floodWhiteAt(
                $mask,
                $x,
                0,
                $black,
                $white
            );

            $this->floodWhiteAt(
                $mask,
                $x,
                $height - 1,
                $black,
                $white
            );
        }

        for (
            $y = 0;
            $y < $height;
            $y += $stepY
        ) {
            $this->floodWhiteAt(
                $mask,
                0,
                $y,
                $black,
                $white
            );

            $this->floodWhiteAt(
                $mask,
                $width - 1,
                $y,
                $black,
                $white
            );
        }

        /*
         * Explicit corners.
         */
        $this->floodWhiteAt(
            $mask,
            0,
            0,
            $black,
            $white
        );

        $this->floodWhiteAt(
            $mask,
            $width - 1,
            0,
            $black,
            $white
        );

        $this->floodWhiteAt(
            $mask,
            0,
            $height - 1,
            $black,
            $white
        );

        $this->floodWhiteAt(
            $mask,
            $width - 1,
            $height - 1,
            $black,
            $white
        );
    }

    private function floodWhiteAt(
        \Imagick $mask,
        int $x,
        int $y,
        \ImagickPixel $fill,
        \ImagickPixel $target
    ): void {
        $pixel =
            $mask->getImagePixelColor(
                $x,
                $y
            );

        $color =
            $pixel->getColor();

        /*
         * Only flood if current point belongs
         * to candidate background.
         */
        if (
            ($color['r'] ?? 0)
            < 128
        ) {
            return;
        }

        $mask->floodFillPaintImage(
            $fill,
            0,
            $target,
            $x,
            $y,
            false
        );
    }

    private function buildConnectedBackgroundMask(
        \Imagick $candidateMask,
        \Imagick $remainingMask
    ): \Imagick {
        $width =
            $candidateMask->getImageWidth();

        $height =
            $candidateMask->getImageHeight();

        $result = new \Imagick();

        $result->newImage(
            $width,
            $height,
            new \ImagickPixel('black'),
            'png'
        );

        $result->setImageColorspace(
            \Imagick::COLORSPACE_GRAY
        );

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $candidate =
                    $candidateMask
                        ->getImagePixelColor(
                            $x,
                            $y
                        )
                        ->getColor();

                $remaining =
                    $remainingMask
                        ->getImagePixelColor(
                            $x,
                            $y
                        )
                        ->getColor();

                $candidateWhite =
                    ($candidate['r'] ?? 0)
                    >= 128;

                $remainingBlack =
                    ($remaining['r'] ?? 0)
                    < 128;

                $result
                    ->getImagePixelColor(
                        $x,
                        $y
                    )
                    ->setColor(
                        (
                            $candidateWhite
                            && $remainingBlack
                        )
                            ? 'white'
                            : 'black'
                    );
            }

            $result->syncImage();
        }

        return $result;
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

        $mime =
            $matches[1]
            ?? 'image/png';

        $binary =
            base64_decode(
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
        return
            'data:'
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
            $this->breakerKey(
                $model
            )
        );
    }

    private function breakerKey(
        string $model
    ): string {
        return
            'genai:breaker:'
            . Str::slug(
                $model
            );
    }

    private function tripBreaker(
        string $model
    ): void {
        Cache::put(
            $this->breakerKey(
                $model
            ),
            1,
            $this->breakerTtlSec
        );
    }

    private function acquireLimiterTokenOrFail(): void
    {
        $minuteKey =
            $this->limitKeyPrefix
            . ':'
            . now()->format(
                'YmdHi'
            );

        $count =
            Cache::increment(
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

        $textTokens =
            (int) ceil(
                mb_strlen($text) / 4
            );

        $inputImageTokens =
            $hasInputImage
                ? 560
                : 0;

        $outputImageTokens =
            1120
            * $outputImages;

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

        $promptLower =
            strtolower(
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

        $tokens =
            random_int(
                700,
                2000
            );

        $imagePath =
            public_path(
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
            mime_content_type(
                $imagePath
            )
                ?: 'image/png';

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
                $this->postProcessTransparentImage(
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
