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

        // reads the 4 corner pixels of the image and returns the most common one as a hex color.
        // corners are the most reliable signal for "background color" without knowing the image upfront.
        $detectBackgroundColor = static function (\Imagick $imagick) {
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();

            $corners = [
                [0, 0],
                [$width - 1, 0],
                [0, $height - 1],
                [$width - 1, $height - 1],
            ];

            $hexes = [];

            foreach ($corners as [$x, $y]) {
                $pixel = $imagick->getImagePixelColor($x, $y);
                $color = $pixel->getColor();
                $hexes[] = sprintf('#%02X%02X%02X', $color['r'], $color['g'], $color['b']);
            }

            $counts = array_count_values($hexes);
            arsort($counts);

            return array_key_first($counts);
        };

        // makes the background of an image transparent and converts it to PNG.
        // if no color is given, it auto-detects it from the image corners.
        // skips svg (vector) and non-image files. falls back to original file on any failure.
        $applyTransparency = static function ($file) use ($makeTransparent, $transparentColor, $fuzzPercent, $detectBackgroundColor) {
            if (!$makeTransparent) {
                return $file;
            }

            if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                return $file;
            }

            $mimeType = $file->getClientMimeType();

            if ($mimeType === 'image/svg+xml') {
                return $file;
            }

            if (!in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
                return $file;
            }

            if (!class_exists(\Imagick::class)) {
                return $file;
            }

            try {
                $imagick = new \Imagick($file->getPathname());

                // Important for JPG/flat PNG
                $imagick->setImageFormat('png');
                $imagick->setImageColorspace(\Imagick::COLORSPACE_SRGB);
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

                $width = $imagick->getImageWidth();
                $height = $imagick->getImageHeight();

                $quantumRange = \Imagick::getQuantumRange()['quantumRangeLong'];
                $fuzzPercent = max(0, min(100, $fuzzPercent));
                $fuzzAbs = ($fuzzPercent / 100) * $quantumRange;

                $transparent = new \ImagickPixel('transparent');

                /*
                 * Remove background connected to image corners only.
                 * This keeps white details inside the logo safe.
                 */
                $corners = [
                    [0, 0],
                    [$width - 1, 0],
                    [0, $height - 1],
                    [$width - 1, $height - 1],
                ];

                foreach ($corners as [$x, $y]) {
                    $targetColor = $transparentColor
                        ? new \ImagickPixel($transparentColor)
                        : $imagick->getImagePixelColor($x, $y);

                    $imagick->floodFillPaintImage(
                        $transparent,
                        $fuzzAbs,
                        $targetColor,
                        $x,
                        $y,
                        false
                    );
                }

                // Clean alpha and force PNG output
                $imagick->setImageFormat('png');

                $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalBase = $originalBase ?: (string) Str::uuid();

                $tempPath = storage_path('app/tmp-transparent/' . Str::uuid() . '.png');

                if (!is_dir(dirname($tempPath))) {
                    mkdir(dirname($tempPath), 0755, true);
                }

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
                    'file_name'             => $file->getClientOriginalName(),
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

                $path = $file->storeAs($directory, $file->getClientOriginalName(), 'public');

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


