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
            $fuzzPercent
        ) {
            if (!$makeTransparent) return $file;

            if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                return $file;
            }

            // Important: use detected mime, not client mime
            $mimeType = $file->getMimeType();

            if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
                return $file;
            }

            if (!class_exists(\Imagick::class)) {
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

                /*
                 * Detect white / off-white background.
                 */
                if ($transparentColor) {
                    $background = new \ImagickPixel($transparentColor);
                } else {
                    $samples = [];

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

                    foreach ($samplePoints as [$x, $y]) {
                        $color = $image->getImagePixelColor($x, $y)->getColor();

                        // take only bright background-looking samples
                        if (
                            $color['r'] >= 190 &&
                            $color['g'] >= 190 &&
                            $color['b'] >= 190
                        ) {
                            $samples[] = $color;
                        }
                    }

                    if (!$samples) {
                        $image->clear();
                        $image->destroy();
                        return $file;
                    }

                    $r = (int) round(array_sum(array_column($samples, 'r')) / count($samples));
                    $g = (int) round(array_sum(array_column($samples, 'g')) / count($samples));
                    $b = (int) round(array_sum(array_column($samples, 'b')) / count($samples));

                    $background = new \ImagickPixel("rgb({$r},{$g},{$b})");
                }

                $transparent = new \ImagickPixel('transparent');

                /*
                 * Add temporary border.
                 *
                 * This guarantees that point 0,0 is definitely background.
                 */
                $image->borderImage($background, 2, 2);

                /*
                 * Remove ONLY the background connected to the outer border.
                 *
                 * White parts inside the product are NOT globally removed.
                 */
                $image->floodFillPaintImage(
                    $transparent,
                    $fuzzAbs,
                    $background,
                    0,
                    0,
                    false
                );

                /*
                 * Remove temporary border.
                 */
                $image->shaveImage(2, 2);

                $image->setImagePage(0, 0, 0, 0);
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
