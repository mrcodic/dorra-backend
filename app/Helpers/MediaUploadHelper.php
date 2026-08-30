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
        float $fuzzPercent = 10,
        bool $removeInnerBackgroundHoles = true,
        float $maxInnerHoleAreaPercent = 3
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

                if (!str_starts_with((string) $file->getClientMimeType(), 'image/')) {
                    return [];
                }

                if (!class_exists(\Imagick::class)) return [];

                $image = new \Imagick();
                $image->pingImage($file->getPathname());

                $dimensions = [
                    'width' => (int) $image->getImageWidth(),
                    'height' => (int) $image->getImageHeight(),
                ];

                $image->clear();
                $image->destroy();

                return $dimensions;
            } catch (\Throwable $e) {
                return [];
            }
        };

        $applyTransparency = static function ($file) use (
            $makeTransparent,
            $transparentColor,
            $fuzzPercent,
            $removeInnerBackgroundHoles,
            $maxInnerHoleAreaPercent
        ) {
            if (!$makeTransparent) return $file;

            if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                return $file;
            }

            $mimeType = $file->getClientMimeType();

            if (
                $mimeType === 'image/svg+xml' ||
                !in_array($mimeType, ['image/jpg', 'image/jpeg', 'image/png'], true) ||
                !class_exists(\Imagick::class)
            ) {
                return $file;
            }

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
                | Edge points
                |--------------------------------------------------------------------------
                */

                $stepX = max(1, (int) floor($width / 50));
                $stepY = max(1, (int) floor($height / 50));

                $edgePoints = [];

                for ($x = 0; $x < $width; $x += $stepX) {
                    $edgePoints[] = [$x, 0];
                    $edgePoints[] = [$x, $height - 1];
                }

                for ($y = 0; $y < $height; $y += $stepY) {
                    $edgePoints[] = [0, $y];
                    $edgePoints[] = [$width - 1, $y];
                }

                $edgePoints[] = [0, 0];
                $edgePoints[] = [$width - 1, 0];
                $edgePoints[] = [0, $height - 1];
                $edgePoints[] = [$width - 1, $height - 1];

                /*
                |--------------------------------------------------------------------------
                | Detect representative white/off-white background
                |--------------------------------------------------------------------------
                */

                if ($transparentColor) {
                    $target = new \ImagickPixel($transparentColor);
                    $backgroundRgb = $target->getColor(true);
                } else {
                    $samples = [];

                    foreach ($edgePoints as [$x, $y]) {
                        $color = $image->getImagePixelColor($x, $y)->getColor(true);

                        $r = $color['r'] ?? 0;
                        $g = $color['g'] ?? 0;
                        $b = $color['b'] ?? 0;
                        $a = $color['a'] ?? 1;

                        if ($a <= 0.01) continue;

                        $min = min($r, $g, $b);
                        $max = max($r, $g, $b);

                        // Only white/off-white edge pixels
                        if ($min >= 0.72 && ($max - $min) <= 0.18) {
                            $samples[] = [$r, $g, $b];
                        }
                    }

                    if (!$samples) {
                        $image->clear();
                        $image->destroy();
                        return $file;
                    }

                    $backgroundRgb = [
                        'r' => array_sum(array_column($samples, 0)) / count($samples),
                        'g' => array_sum(array_column($samples, 1)) / count($samples),
                        'b' => array_sum(array_column($samples, 2)) / count($samples),
                    ];
                }

                $similarityTolerance = max(
                    0.05,
                    min(0.30, ($fuzzPercent / 100) + 0.04)
                );

                $isBackgroundLike = static function (\ImagickPixel $pixel) use (
                    $backgroundRgb,
                    $similarityTolerance
                ) {
                    $color = $pixel->getColor(true);

                    if (($color['a'] ?? 1) <= 0.01) return false;

                    $distance = max(
                        abs(($color['r'] ?? 0) - $backgroundRgb['r']),
                        abs(($color['g'] ?? 0) - $backgroundRgb['g']),
                        abs(($color['b'] ?? 0) - $backgroundRgb['b'])
                    );

                    return $distance <= $similarityTolerance;
                };

                /*
                |--------------------------------------------------------------------------
                | Pass 1: Remove background connected to image edges
                |--------------------------------------------------------------------------
                */

                foreach ($edgePoints as [$x, $y]) {
                    $seed = $image->getImagePixelColor($x, $y);

                    if (!$isBackgroundLike($seed)) continue;

                    $seedColor = clone $seed;

                    $image->floodFillPaintImage(
                        $transparent,
                        $fuzzAbs,
                        $seedColor,
                        $x,
                        $y,
                        false
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Pass 2: Remove enclosed background holes
                |--------------------------------------------------------------------------
                |
                | Examples:
                | - inside mug handle
                | - inside O
                | - inside D
                | - inside A
                |
                | A component is removed only when:
                | 1. it looks like the detected background
                | 2. it does NOT touch existing transparency
                | 3. it isn't too large
                |
                */

                if ($removeInnerBackgroundHoles) {
                    $work = clone $image;

                    $scanStep = 2;
                    $marker = new \ImagickPixel('#FF00FF');
                    $processed = new \ImagickPixel('#000000');

                    $maxPixels = (int) (
                        ($width * $height) *
                        (max(0, $maxInnerHoleAreaPercent) / 100)
                    );

                    $isMarker = static function (\ImagickPixel $pixel) {
                        $color = $pixel->getColor(true);

                        return
                            ($color['r'] ?? 0) >= 0.98 &&
                            ($color['g'] ?? 0) <= 0.02 &&
                            ($color['b'] ?? 0) >= 0.98;
                    };

                    for ($y = 1; $y < $height - 1; $y += $scanStep) {
                        for ($x = 1; $x < $width - 1; $x += $scanStep) {
                            $workPixel = $work->getImagePixelColor($x, $y);

                            if (!$isBackgroundLike($workPixel)) continue;

                            $originalPixel = $image->getImagePixelColor($x, $y);
                            $originalColor = $originalPixel->getColor(true);

                            if (($originalColor['a'] ?? 1) <= 0.01) {
                                continue;
                            }

                            $seedColor = clone $workPixel;

                            /*
                             * Mark this connected component only.
                             */
                            $work->floodFillPaintImage(
                                $marker,
                                $fuzzAbs,
                                $seedColor,
                                $x,
                                $y,
                                false
                            );

                            /*
                             * Isolate current marker component and get bbox.
                             */
                            $component = clone $work;

                            $component->transparentPaintImage(
                                $marker,
                                0,
                                0,
                                true
                            );

                            $component->trimImage(0);

                            $page = $component->getImagePage();

                            $boxX = max(0, (int) ($page['x'] ?? 0));
                            $boxY = max(0, (int) ($page['y'] ?? 0));
                            $boxWidth = (int) $component->getImageWidth();
                            $boxHeight = (int) $component->getImageHeight();

                            $boxWidth = min($boxWidth, $width - $boxX);
                            $boxHeight = min($boxHeight, $height - $boxY);

                            $component->clear();
                            $component->destroy();

                            $touchesTransparency = false;
                            $componentPixels = 0;

                            /*
                             * Check whether component touches already transparent
                             * outer background.
                             */
                            for ($cy = $boxY; $cy < $boxY + $boxHeight; $cy++) {
                                for ($cx = $boxX; $cx < $boxX + $boxWidth; $cx++) {
                                    $pixel = $work->getImagePixelColor($cx, $cy);

                                    if (!$isMarker($pixel)) continue;

                                    $componentPixels++;

                                    foreach ([
                                                 [-1, -1], [0, -1], [1, -1],
                                                 [-1, 0],           [1, 0],
                                                 [-1, 1],  [0, 1],  [1, 1],
                                             ] as [$dx, $dy]) {
                                        $nx = $cx + $dx;
                                        $ny = $cy + $dy;

                                        if (
                                            $nx < 0 ||
                                            $ny < 0 ||
                                            $nx >= $width ||
                                            $ny >= $height
                                        ) {
                                            $touchesTransparency = true;
                                            break 3;
                                        }

                                        $neighbor = $image
                                            ->getImagePixelColor($nx, $ny)
                                            ->getColor(true);

                                        if (($neighbor['a'] ?? 1) <= 0.01) {
                                            $touchesTransparency = true;
                                            break 3;
                                        }
                                    }
                                }
                            }

                            /*
                             * Remove only enclosed, reasonably-sized
                             * background components.
                             */
                            if (
                                !$touchesTransparency &&
                                $componentPixels > 0 &&
                                $componentPixels <= $maxPixels
                            ) {
                                $image->floodFillPaintImage(
                                    $transparent,
                                    $fuzzAbs,
                                    $image->getImagePixelColor($x, $y),
                                    $x,
                                    $y,
                                    false
                                );
                            }

                            /*
                             * Mark as processed so we don't scan the
                             * same component again.
                             */
                            $work->floodFillPaintImage(
                                $processed,
                                0,
                                $marker,
                                $x,
                                $y,
                                false
                            );
                        }
                    }

                    $work->clear();
                    $work->destroy();
                }

                /*
                |--------------------------------------------------------------------------
                | Save PNG
                |--------------------------------------------------------------------------
                */

                $image->setImageFormat('png');

                $originalBase = pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $originalBase = $originalBase ?: (string) Str::uuid();

                $tempPath = storage_path(
                    'app/tmp-transparent/' . Str::uuid() . '.png'
                );

                if (!is_dir(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

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

                if ($finalCustomProperties) {
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
                'mime_type' => $file->getClientMimeType(),
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
