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
    function handleMediaUploads($files, $modelData = null, string $collectionName = null, array $customProperties = [], bool $clearExisting = false)
    {
        if (empty($files)) {
            return null;
        }

        $collectionName = $collectionName
            ? getMediaCollectionName($collectionName)
            : ($modelData ? getMediaCollectionName($modelData) : 'default');

        $files = is_array($files) ? Arr::flatten($files) : [$files];

        $uploaded = collect($files)->map(function ($file) use ($modelData, $collectionName, $customProperties) {
            if ($modelData) {
                $mediaAdder = $modelData->addMedia($file);
                if (!empty($customProperties)) {
                    $mediaAdder->withCustomProperties($customProperties);
                }
                return $mediaAdder->toMediaCollection($collectionName);
            } else {
                // validate file
                if (!($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) {
                    throw new \Exception("Invalid file upload");
                }

                // create media db record first
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::create([
                    'collection_name'       => $collectionName,
                    'name'                  => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_name'             => $file->getClientOriginalName(),
                    'mime_type'             => $file->getClientMimeType(),
                    'disk'                  => 'public',
                    'conversions_disk'      => 'public',
                    'size'                  => $file->getSize(),
                    'custom_properties'     => $customProperties,
                    'manipulations'         => [],
                    'responsive_images'     => [],
                    'generated_conversions' => [],
                ]);

                // directory by ID
                $directory = (string) $media->id;

                // now actually store file safely
                $path = $file->storeAs($directory, $file->getClientOriginalName(), 'public');

                // update with the stored filename
                $media->update(['file_name' => basename($path)]);

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
}


