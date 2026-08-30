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
        ?string $transparentColor = null,
        float $fuzzPercent = 10
    ) {
        if (empty($files)) {
            return null;
        }

        $collectionName = $collectionName
            ? getMediaCollectionName($collectionName)
            : ($modelData ? getMediaCollectionName($modelData) : 'default');

        $files = is_array($files) ? Arr::flatten($files) : [$files];

        if ($clearExisting && $modelData) {
            $modelData->clearMediaCollection($collectionName);
        }

        $makeNames = static function ($originalName) {
            $base = pathinfo($originalName, PATHINFO_FILENAME);
            $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $slug = Str::slug($base);

            if ($slug === '' || $slug === null) {
                $slug = (string) Str::uuid();
            }

            $safeFileName = $slug . ($ext ? ".{$ext}" : '');
            $humanName = $base ?: $slug;

            return [$humanName, $safeFileName, $ext];
        };

        $getImageDimensions = static function ($file) {
            try {
                if (!($file instanceof \Illuminate\Http\UploadedFile)) {
                    return [];
                }

                if (!$file->isValid()) {
                    return [];
                }

                $mimeType = $file->getClientMimeType();

                if (!str_starts_with((string) $mimeType, 'image/')) {
                    return [];
                }

                if (!class_exists(\Imagick::class)) {
                    return [];
                }

                $imagick = new \Imagick();
                $imagick->pingImage($file->getPathname());

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();

                $imagick->clear();
                $imagick->destroy();

                if (!$width || !$height) {
                    return [];
                }

                return [
                    'width' => (int) $width,
                    'height' => (int) $height,
                ];
            } catch (\Throwable $e) {
                return [];
            }
        };
        $sampleColor = static function (\Imagick $img, int $cx, int $cy, int $w, int $h) {
            $size = 5;
            $x0 = max(0, min($cx - intdiv($size, 2), max($w - 1, 0)));
            $y0 = max(0, min($cy - intdiv($size, 2), max($h - 1, 0)));
            $sw = max(1, min($size, $w - $x0));
            $sh = max(1, min($size, $h - $y0));

            $clone = clone $img;
            $clone->cropImage($sw, $sh, $x0, $y0);
            $clone->resizeImage(1, 1, \Imagick::FILTER_BOX, 1);

            $pixel = $clone->getImagePixelColor(0, 0);

            $clone->clear();
            $clone->destroy();

            return $pixel;
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

            if (!class_exists(\Imagick::class)) {
                return $file;
            }

            try {
                $imagick = new \Imagick($file->getPathname());

                $imagick->setImageFormat('png');
                $imagick->setImageColorspace(\Imagick::COLORSPACE_SRGB);
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();

                $quantumRange = \Imagick::getQuantumRange()['quantumRangeLong'];

                $fuzzPercent = max(0, min(100, $fuzzPercent));
                $fuzzAbs = ($fuzzPercent / 100) * $quantumRange;

                /*
                |--------------------------------------------------------------------------
                | Detect background
                |--------------------------------------------------------------------------
                */

                if ($transparentColor) {
                    $background = new \ImagickPixel($transparentColor);
                } else {
                    $points = [
                        [2, 2],
                        [$width - 3, 2],
                        [2, $height - 3],
                        [$width - 3, $height - 3],
                        [(int) ($width / 2), 2],
                        [(int) ($width / 2), $height - 3],
                        [2, (int) ($height / 2)],
                        [$width - 3, (int) ($height / 2)],
                    ];

                    $colors = [];

                    foreach ($points as [$x, $y]) {
                        $color = $imagick
                            ->getImagePixelColor($x, $y)
                            ->getColor();

                        if (
                            $color['r'] >= 180 &&
                            $color['g'] >= 180 &&
                            $color['b'] >= 180
                        ) {
                            $colors[] = $color;
                        }
                    }

                    if (!$colors) {
                        $imagick->clear();
                        $imagick->destroy();

                        return $file;
                    }

                    $r = (int) round(
                        array_sum(array_column($colors, 'r')) / count($colors)
                    );

                    $g = (int) round(
                        array_sum(array_column($colors, 'g')) / count($colors)
                    );

                    $b = (int) round(
                        array_sum(array_column($colors, 'b')) / count($colors)
                    );

                    $background = new \ImagickPixel(
                        "rgb({$r},{$g},{$b})"
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Remove ONLY outer connected background
                |--------------------------------------------------------------------------
                */

                $transparent = new \ImagickPixel('transparent');

                /*
                 * Temporary border guarantees that 0,0 is background.
                 */
                $border = 3;

                $imagick->borderImage(
                    $background,
                    $border,
                    $border
                );

                $imagick->floodFillPaintImage(
                    $transparent,
                    $fuzzAbs,
                    $background,
                    0,
                    0,
                    false
                );

                $imagick->shaveImage(
                    $border,
                    $border
                );

                $imagick->setImagePage(0, 0, 0, 0);

                /*
                |--------------------------------------------------------------------------
                | Clean thin white fringe
                |--------------------------------------------------------------------------
                |
                | Shrink alpha by about 1 pixel.
                | This removes the white background halo without globally
                | deleting white pixels inside the product.
                */

                $alpha = clone $imagick;

                $alpha->separateImageChannel(
                    \Imagick::CHANNEL_ALPHA
                );

                $alpha->morphology(
                    \Imagick::MORPHOLOGY_ERODE,
                    1,
                    'Disk:1'
                );

                $imagick->compositeImage(
                    $alpha,
                    \Imagick::COMPOSITE_COPYOPACITY,
                    0,
                    0
                );

                $alpha->clear();
                $alpha->destroy();

                /*
                |--------------------------------------------------------------------------
                | Save
                |--------------------------------------------------------------------------
                */

                $imagick->setImageFormat('png');

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

                $imagick->writeImage($tempPath);

                $imagick->clear();
                $imagick->destroy();

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

            [$humanName, $safeFileName, $ext] = $makeNames($file->getClientOriginalName());

            $imageDimensions = $getImageDimensions($file);

            $finalCustomProperties = array_merge(
                $customProperties,
                $imageDimensions
            );

            if ($modelData) {
                $mediaAdder = $modelData->addMedia($file)
                    ->usingName($humanName)
                    ->usingFileName($safeFileName);

                if (!empty($finalCustomProperties)) {
                    $mediaAdder->withCustomProperties($finalCustomProperties);
                }

                return $mediaAdder->toMediaCollection($collectionName);
            } else {
                if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                    throw new \Exception("Invalid file upload");
                }

                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
                    'collection_name'       => $collectionName,
                    'name'                  => $humanName,
                    'file_name'             => $safeFileName,
                    'mime_type'             => $file->getClientMimeType(),
                    'disk'                  => 'public',
                    'conversions_disk'      => 'public',
                    'size'                  => $file->getSize(),
                    'custom_properties'     => $finalCustomProperties,
                    'manipulations'         => [],
                    'responsive_images'     => [],
                    'generated_conversions' => [],
                ]);

                $directory = (string) $media->id;

                $path = $file->storeAs($directory, $safeFileName, 'public');

                $media->update([
                    'file_name' => basename($path),
                ]);

                return $media;
            }
        });

        return count($uploaded) === 1 ? $uploaded->first() : $uploaded;
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
