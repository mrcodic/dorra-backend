<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

if (!function_exists('getMediaCollectionName')) {
    function getMediaCollectionName($modelData): string
    {
        if (is_string($modelData)) {
            return $modelData;
        }
        return Str::plural(Str::lcfirst(class_basename($modelData)));

    }
}

if (!function_exists('handleMediaUploads')) {
    function handleMediaUploads(
        $files,
        $modelData = null,
        string $collectionName = null,
        array $customProperties = [],
        bool $clearExisting = false,
        $columns = null,
        bool $makeTransparent = false,
        ?string $transparentColor = '#FFFFFF',
        float $fuzzPercent = 8
    ) {
        if (empty($files)) return null;

        $collectionName = $collectionName
            ? getMediaCollectionName($collectionName)
            : ($modelData ? getMediaCollectionName($modelData) : 'default');

        $files = is_array($files) ? Arr::flatten($files) : [$files];

        if ($clearExisting && $modelData) {
            $modelData->clearMediaCollection($collectionName);
        }

        $makeNames = static function ($originalName) {
            $base = pathinfo($originalName, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $slug = Str::slug($base);

            if (!$slug) $slug = (string) Str::uuid();

            return [
                $base ?: $slug,
                $slug . ($ext ? ".{$ext}" : ''),
                $ext
            ];
        };

        $getImageDimensions = static function ($file) {
            try {
                if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                    return [];
                }

                if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
                    return [];
                }

                if (!class_exists(\Imagick::class)) return [];

                $image = new \Imagick();
                $image->pingImage($file->getPathname());

                $data = [
                    'width' => (int) $image->getImageWidth(),
                    'height' => (int) $image->getImageHeight(),
                ];

                $image->clear();
                $image->destroy();

                return $data;
            } catch (\Throwable $e) {
                return [];
            }
        };

        $applyTransparency = static function ($file) use (
            $makeTransparent,
            $transparentColor,
            $fuzzPercent
        ) {
            if (!$makeTransparent) return $file;

            if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                return $file;
            }

            $mimeType = $file->getMimeType();

            if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
                return $file;
            }

            if (!class_exists(\Imagick::class)) return $file;

            try {
                $image = new \Imagick($file->getPathname());

                $image->setImageFormat('png');
                $image->setImageColorspace(\Imagick::COLORSPACE_SRGB);
                $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                $width = $image->getImageWidth();
                $height = $image->getImageHeight();

                $quantumRange = \Imagick::getQuantumRange()['quantumRangeLong'];
                $fuzzPercent = max(0, min(100, $fuzzPercent));
                $fuzzAbs = ($fuzzPercent / 100) * $quantumRange;

                $transparent = new \ImagickPixel('transparent');

                /*
                |--------------------------------------------------------------------------
                | Detect background color
                |--------------------------------------------------------------------------
                */

                if ($transparentColor) {
                    $background = new \ImagickPixel($transparentColor);
                } else {
                    $samplePoints = [
                        [2, 2],
                        [$width - 3, 2],
                        [2, $height - 3],
                        [$width - 3, $height - 3],
                        [(int) ($width / 2), 2],
                        [(int) ($width / 2), $height - 3],
                        [2, (int) ($height / 2)],
                        [$width - 3, (int) ($height / 2)],
                    ];

                    $samples = [];

                    foreach ($samplePoints as [$x, $y]) {
                        $color = $image
                            ->getImagePixelColor($x, $y)
                            ->getColor();

                        if (
                            $color['r'] >= 180 &&
                            $color['g'] >= 180 &&
                            $color['b'] >= 180
                        ) {
                            $samples[] = $color;
                        }
                    }

                    if (!$samples) {
                        $image->clear();
                        $image->destroy();

                        return $file;
                    }

                    $r = (int) round(
                        array_sum(array_column($samples, 'r')) / count($samples)
                    );

                    $g = (int) round(
                        array_sum(array_column($samples, 'g')) / count($samples)
                    );

                    $b = (int) round(
                        array_sum(array_column($samples, 'b')) / count($samples)
                    );

                    $background = new \ImagickPixel(
                        "rgb({$r},{$g},{$b})"
                    );
                }

                $backgroundNormalized = $background->getColor(true);

                /*
                |--------------------------------------------------------------------------
                | Remove outer connected background
                |--------------------------------------------------------------------------
                */

                $borderSize = 3;

                $image->borderImage(
                    $background,
                    $borderSize,
                    $borderSize
                );

                $image->floodFillPaintImage(
                    $transparent,
                    $fuzzAbs,
                    $background,
                    0,
                    0,
                    false
                );

                $image->shaveImage(
                    $borderSize,
                    $borderSize
                );

                $image->setImagePage(0, 0, 0, 0);

                /*
                |--------------------------------------------------------------------------
                | Remove enclosed background holes
                |--------------------------------------------------------------------------
                |
                | Examples:
                | - inside mug handle
                | - inside O
                | - inside D
                | - inside A
                |
                | We only accept regions that appear enclosed by foreground
                | on all four sides.
                */

                $isBackgroundLike = static function (\ImagickPixel $pixel) use (
                    $backgroundNormalized,
                    $fuzzPercent
                ) {
                    $color = $pixel->getColor(true);

                    if (($color['a'] ?? 1) <= 0.05) {
                        return false;
                    }

                    $tolerance = max(
                        0.05,
                        min(0.25, ($fuzzPercent / 100) + 0.04)
                    );

                    $distance = max(
                        abs(($color['r'] ?? 0) - ($backgroundNormalized['r'] ?? 1)),
                        abs(($color['g'] ?? 0) - ($backgroundNormalized['g'] ?? 1)),
                        abs(($color['b'] ?? 0) - ($backgroundNormalized['b'] ?? 1))
                    );

                    return $distance <= $tolerance;
                };

                $scanStep = max(
                    2,
                    (int) floor(min($width, $height) / 250)
                );

                $maxSearchDistance = (int) (
                    min($width, $height) * 0.35
                );

                $findForegroundBoundary = static function (
                    $startX,
                    $startY,
                    $dx,
                    $dy
                ) use (
                    $image,
                    $width,
                    $height,
                    $isBackgroundLike,
                    $maxSearchDistance
                ) {
                    $x = $startX;
                    $y = $startY;
                    $distance = 0;

                    while (
                        $x >= 0 &&
                        $y >= 0 &&
                        $x < $width &&
                        $y < $height &&
                        $distance < $maxSearchDistance
                    ) {
                        $pixel = $image->getImagePixelColor($x, $y);
                        $color = $pixel->getColor(true);

                        if (($color['a'] ?? 1) <= 0.05) {
                            return false;
                        }

                        if (!$isBackgroundLike($pixel)) {
                            return true;
                        }

                        $x += $dx;
                        $y += $dy;
                        $distance++;
                    }

                    return false;
                };

                for ($y = $scanStep; $y < $height - $scanStep; $y += $scanStep) {
                    for ($x = $scanStep; $x < $width - $scanStep; $x += $scanStep) {
                        $pixel = $image->getImagePixelColor($x, $y);

                        if (!$isBackgroundLike($pixel)) {
                            continue;
                        }

                        $left = $findForegroundBoundary($x, $y, -1, 0);
                        if (!$left) continue;

                        $right = $findForegroundBoundary($x, $y, 1, 0);
                        if (!$right) continue;

                        $top = $findForegroundBoundary($x, $y, 0, -1);
                        if (!$top) continue;

                        $bottom = $findForegroundBoundary($x, $y, 0, 1);
                        if (!$bottom) continue;

                        /*
                         * This looks like an enclosed background region.
                         */
                        $seed = clone $pixel;

                        $image->floodFillPaintImage(
                            $transparent,
                            $fuzzAbs,
                            $seed,
                            $x,
                            $y,
                            false
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Remove white anti-alias fringe
                |--------------------------------------------------------------------------
                |
                | Shrink opacity around the object by about one pixel.
                | This targets the remaining white halo around edges.
                */

                $alpha = clone $image;

                $alpha->setImageAlphaChannel(
                    \Imagick::ALPHACHANNEL_EXTRACT
                );

                $alpha->morphology(
                    \Imagick::MORPHOLOGY_ERODE,
                    1,
                    'Disk:1'
                );

                $alpha->setImageAlphaChannel(
                    \Imagick::ALPHACHANNEL_DEACTIVATE
                );

                $image->compositeImage(
                    $alpha,
                    \Imagick::COMPOSITE_COPYOPACITY,
                    0,
                    0
                );

                $alpha->clear();
                $alpha->destroy();

                /*
                |--------------------------------------------------------------------------
                | Save final transparent PNG
                |--------------------------------------------------------------------------
                */

                $image->setImageFormat('png');

                $originalBase = pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $originalBase = $originalBase ?: (string) Str::uuid();

                $tempDir = storage_path('app/tmp-transparent');

                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $tempPath = $tempDir . '/' . Str::uuid() . '.png';

                $image->writeImage($tempPath);

                $image->clear();
                $image->destroy();

                return new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $originalBase . '.png',
                    'image/png',
                    null,
                    true
                );
            } catch (\Throwable $e) {
                report($e);

                return $file;
            }
        };

        $uploaded = collect($files)->map(function ($file) use (
            $modelData,
            $collectionName,
            $customProperties,
            $makeNames,
            $getImageDimensions,
            $applyTransparency
        ) {
            $file = $applyTransparency($file);

            [$humanName, $safeFileName] = $makeNames(
                $file->getClientOriginalName()
            );

            $finalCustomProperties = array_merge(
                $customProperties,
                $getImageDimensions($file)
            );

            if ($modelData) {
                $mediaAdder = $modelData
                    ->addMedia($file)
                    ->usingName($humanName)
                    ->usingFileName($safeFileName);

                if (!empty($finalCustomProperties)) {
                    $mediaAdder->withCustomProperties(
                        $finalCustomProperties
                    );
                }

                return $mediaAdder->toMediaCollection(
                    $collectionName
                );
            }

            if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
                'collection_name' => $collectionName,
                'name' => $humanName,
                'file_name' => $safeFileName,
                'mime_type' => $file->getMimeType(),
                'disk' => 'public',
                'conversions_disk' => 'public',
                'size' => $file->getSize(),
                'custom_properties' => $finalCustomProperties,
                'manipulations' => [],
                'responsive_images' => [],
                'generated_conversions' => [],
            ]);

            $path = $file->storeAs(
                (string) $media->id,
                $safeFileName,
                'public'
            );

            $media->update([
                'file_name' => basename($path),
            ]);

            return $media;
        });

        return count($uploaded) === 1
            ? $uploaded->first()
            : $uploaded;
    }
}
if (!function_exists('clearMediaCollections')) {
    function clearMediaCollections($modelData, ?array $collections = []): void
    {
        if (empty($collections)) {
            $collectionName = getMediaCollectionName($modelData);
            $modelData->clearMediaCollection($collectionName);
            return;
        }
        collect($collections)->map(function ($collection) use ($modelData) {
            $modelData->clearMediaCollection($collection);
        });
    }
}

if (!function_exists('deleteMediaById')) {
    function deleteMediaById($uuid): void
    {
        $media = Media::find($uuid);
        $media->delete();

    }
}

if (!function_exists('deleteMediaByCustomProperty')) {
    function deleteMediaByCustomProperty($key, $collectionName, $id): void
    {
        $media = Media::query()->where('collection_name', $collectionName)
            ->whereJsonContains('custom_properties->key', $key)
            ->whereModelId($id)
            ->first();

        $media?->delete();
        if ($media) {
            Storage::disk($media?->disk)->delete($media?->getPathRelativeToRoot());

        }

    }
}

if (!function_exists('addMediaToResource')) {
    function addMediaToResource($files, $modelData, string $collectionName = null, array $customProperties = [], bool $clearExisting = false)
    {
        if (empty($files)) {
            return null;
        }

        $files = is_array($files) ? Arr::flatten($files) : [$files];

        $collectionName = $collectionName ? getMediaCollectionName($collectionName) : getMediaCollectionName($modelData);

        if ($clearExisting) {
            $modelData?->clearMediaCollection($collectionName);
        }

        $uploaded = collect($files)->map(function ($file) use ($modelData, $collectionName, $customProperties) {
            if (!$modelData) {
                return null;
            }

            $mediaAdder = $modelData->addMedia($file);

            if (!empty($customProperties)) {
                $mediaAdder->withCustomProperties($customProperties);
            }

            return $mediaAdder->toMediaCollection($collectionName);
        })->filter();

        return count($uploaded) === 1 ? $uploaded->first() : $uploaded->all();
    }

    if (!function_exists('attachMediaToModel')) {
        function attachMediaToModel(
            ?int $mediaId,
                 $model,
            string $collectionName = null,
            bool $clearExisting = false
        ): ?Media {
            if (!$mediaId) {
                return null;
            }

            $media = Media::find($mediaId);

            if (!$media) {
                return null;
            }

            $collectionName = $collectionName
                ? getMediaCollectionName($collectionName)
                : getMediaCollectionName($model);

            if ($clearExisting && method_exists($model, 'getFirstMedia')) {
                $currentMedia = $model->getFirstMedia($collectionName);

                if ($currentMedia && (int) $currentMedia->id !== (int) $mediaId) {
                    $model->clearMediaCollection($collectionName);
                }
            } elseif ($clearExisting && method_exists($model, 'clearMediaCollection')) {
                $model->clearMediaCollection($collectionName);
            }
            $media->model_type = get_class($model);
            $media->model_id = $model->getKey();
            $media->collection_name = $collectionName;
            $media->save();

            return $media;
        }
    }
}
