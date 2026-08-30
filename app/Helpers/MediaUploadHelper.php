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
            $fuzzPercent,
            $sampleColor
        ) {
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

            if (!in_array($mimeType, ['image/jpg', 'image/jpeg', 'image/png'], true)) {
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

                $transparent = new \ImagickPixel('transparent');

                $stepX = max(1, (int) floor($width / 50));
                $stepY = max(1, (int) floor($height / 50));

                $points = [];

                for ($x = 0; $x < $width; $x += $stepX) {
                    $points[] = [$x, 0];
                    $points[] = [$x, $height - 1];
                }

                for ($y = 0; $y < $height; $y += $stepY) {
                    $points[] = [0, $y];
                    $points[] = [$width - 1, $y];
                }

                $edgeSamples = [];

                foreach ($points as [$x, $y]) {
                    $pixel = $imagick->getImagePixelColor($x, $y);

                    $edgeSamples[] = [
                        'x' => $x,
                        'y' => $y,
                        'color' => clone $pixel,
                    ];
                }

                foreach ($edgeSamples as $sample) {
                    $x = $sample['x'];
                    $y = $sample['y'];

                    $currentPixel = $imagick->getImagePixelColor($x, $y);
                    $currentColor = $currentPixel->getColor(true);

                    // already transparent
                    if (($currentColor['a'] ?? 1) <= 0.01) {
                        continue;
                    }

                    $imagick->floodFillPaintImage(
                        $transparent,
                        $fuzzAbs,
                        $sample['color'],
                        $x,
                        $y,
                        false
                    );
                }
                $points[] = [0, 0];
                $points[] = [$width - 1, 0];
                $points[] = [0, $height - 1];
                $points[] = [$width - 1, $height - 1];

                $visited = [];

                /*
                 * المرحلة 1:
                 * إزالة الخلفية البيضاء المتصلة بحواف الصورة فقط
                 */
                foreach ($points as [$x, $y]) {
                    $key = "{$x}-{$y}";

                    if (isset($visited[$key])) {
                        continue;
                    }

                    $visited[$key] = true;

                    $targetColor = $transparentColor
                        ? new \ImagickPixel($transparentColor)
                        : $sampleColor($imagick, $x, $y, $width, $height);

                    $imagick->floodFillPaintImage(
                        $transparent,
                        $fuzzAbs,
                        $targetColor,
                        $x,
                        $y,
                        false
                    );
                }

                /*
                 * المرحلة 2:
                 * إزالة الفراغات البيضاء الصغيرة المغلقة داخل الحروف
                 * بدون لمس المساحات البيضاء الكبيرة مثل جسم العربية
                 */
                $removeInnerWhiteHoles = true;
                $maxHoleArea = 6000; // جربي 2000 أو 4000 أو 6000 حسب الصور
                $holeStep = 6;

                if ($removeInnerWhiteHoles) {
                    for ($y = 1; $y < $height - 1; $y += $holeStep) {
                        for ($x = 1; $x < $width - 1; $x += $holeStep) {
                            $pixel = $imagick->getImagePixelColor($x, $y);
                            $color = $pixel->getColor(true);

                            $alpha = $color['a'] ?? 1;
                            $r = ($color['r'] ?? 0) * 255;
                            $g = ($color['g'] ?? 0) * 255;
                            $b = ($color['b'] ?? 0) * 255;

                            // لو البيكسل already transparent نتخطاه
                            if ($alpha <= 0.01) {
                                continue;
                            }

                            // نعتبره أبيض/شبه أبيض
                            $isNearWhite = $r >= 245 && $g >= 245 && $b >= 245;

                            if (!$isNearWhite) {
                                continue;
                            }

                            // فحص تقريبي للمساحة قبل الإزالة
                            $probe = clone $imagick;
                            $probe->floodFillPaintImage(
                                new \ImagickPixel('red'),
                                $fuzzAbs,
                                new \ImagickPixel('#FFFFFF'),
                                $x,
                                $y,
                                false
                            );

                            $componentMask = clone $probe;
                            $componentMask->transparentPaintImage(
                                new \ImagickPixel('red'),
                                0,
                                0,
                                false
                            );

                            $bbox = $componentMask->getImagePage();
                            $area = ($bbox['width'] ?? 0) * ($bbox['height'] ?? 0);

                            $probe->clear();
                            $probe->destroy();
                            $componentMask->clear();
                            $componentMask->destroy();

                            /*
                             * لو المساحة صغيرة نعتبرها hole داخل حرف/لوجو
                             * ونحولها transparent
                             */
                            if ($area > 0 && $area <= $maxHoleArea) {
                                $imagick->floodFillPaintImage(
                                    $transparent,
                                    $fuzzAbs,
                                    new \ImagickPixel('#FFFFFF'),
                                    $x,
                                    $y,
                                    false
                                );
                            }
                        }
                    }
                }

                $imagick->setImageFormat('png');

                $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalBase = $originalBase ?: (string) \Illuminate\Support\Str::uuid();

                $tempPath = storage_path('app/tmp-transparent/' . \Illuminate\Support\Str::uuid() . '.png');

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
