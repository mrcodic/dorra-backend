<?php

namespace App\Services;

use Imagick;
use ImagickPixel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageService
{
    public function processUploaded(int $mediaId, string $collectionName = 'templates'): array
    {
        $original = Media::findOrFail($mediaId);

        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        if (!file_exists($filePath)) {
            throw new \Exception("Media file not found: {$filePath}");
        }

        $imagick = new Imagick($filePath . '[0]');

        $width  = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        // Validate minimum dimensions
        $this->validateDimensions($width, $height);

        $original->update([
            'custom_properties' => array_merge(
                $original->custom_properties ?? [],
                [
                    'width'     => $width,
                    'height'    => $height,
                    'has_alpha' => (bool) $imagick->getImageAlphaChannel(),
                ]
            ),
        ]);

        $imagick->destroy();

        $previewMedia = $this->storePreview($original, $collectionName . '-preview');

        $original->update([
            'custom_properties' => array_merge(
                $original->custom_properties ?? [],
                ['preview_id' => $previewMedia->id]
            ),
        ]);

        return [
            'original_media_id' => $original->id,
            'preview_media_id'  => $previewMedia->id,
        ];
    }

    private function validateDimensions(int $width, int $height): void
    {
        $minWidth  = config('media.original.min_width',  520);
        $minHeight = config('media.original.min_height', 618);

        if ($width < $minWidth || $height < $minHeight) {
            throw new \InvalidArgumentException(
                "Image too small. Minimum dimensions are {$minWidth}×{$minHeight}px. Uploaded: {$width}×{$height}px."
            );
        }
    }

    private function storePreview(Media $original, string $previewCollection): Media
    {
        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        $preview = new Imagick($filePath . '[0]');

        // Strip metadata first
        $preview->stripImage();

        // Compress down to 1MB max — reduce quality in steps until under limit
        $this->compressToLimit(
            imagick  : $preview,
            maxBytes : config('media.preview.max_size', 1 * 1024 * 1024), // 1MB
        );

        // Preserve alpha if original had it
        if ($preview->getImageAlphaChannel()) {
            $preview->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $preview->setBackgroundColor(new ImagickPixel('transparent'));
        }

        $ext             = pathinfo($original->file_name, PATHINFO_EXTENSION);
        $tmpPath         = tempnam(sys_get_temp_dir(), 'preview_') . '.' . $ext;
        $previewFileName = pathinfo($original->file_name, PATHINFO_FILENAME) . '_preview.' . $ext;

        $preview->writeImage($tmpPath);

        $previewMedia = handleMediaUploads(
            files           : new UploadedFile(
                path        : $tmpPath,
                originalName: $previewFileName,
                mimeType    : $original->mime_type,
                test        : true,
            ),
            modelData       : $original->model,
            collectionName  : $previewCollection,
            customProperties: [
                'width'       => $preview->getImageWidth(),
                'height'      => $preview->getImageHeight(),
                'has_alpha'   => (bool) $preview->getImageAlphaChannel(),
                'original_id' => $original->id,
            ],
        );

        $preview->destroy();
        @unlink($tmpPath);

        return $previewMedia;
    }

    private function compressToLimit(Imagick $imagick, int $maxBytes): void
    {
        $quality = 85; // start quality
        $step    = 5;  // reduce by 5 each iteration
        $minQuality = 10; // never go below this

        $imagick->setImageCompressionQuality($quality);

        // If already under limit — nothing to do
        if (strlen($imagick->getImageBlob()) <= $maxBytes) {
            return;
        }

        // Reduce quality in steps until under 1MB or hit minimum quality
        while ($quality > $minQuality) {
            $quality -= $step;
            $imagick->setImageCompressionQuality($quality);

            if (strlen($imagick->getImageBlob()) <= $maxBytes) {
                break;
            }
        }
    }
}
