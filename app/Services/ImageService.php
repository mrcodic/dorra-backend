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

//        if ($width < $minWidth || $height < $minHeight) {
//            throw new \InvalidArgumentException(
//                "Image too small. Minimum dimensions are {$minWidth}×{$minHeight}px. Uploaded: {$width}×{$height}px."
//            );
//        }
    }

    private function storePreview(Media $original, string $previewCollection): Media
    {
        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        $preview = new Imagick($filePath . '[0]');

        // Remove metadata only
        $preview->stripImage();

        // ✅ Keep original format — no conversion to JPEG
        $originalFormat = strtolower($preview->getImageFormat()); // e.g. 'png', 'webp'
        $originalMime   = $this->getMimeType($originalFormat);

        // ✅ Set compression based on format
        match ($originalFormat) {
            'png'  => $preview->setImageCompression(Imagick::COMPRESSION_ZIP),
            'webp' => $preview->setImageCompressionQuality(85),
            default => null,
        };

        // ✅ NO alpha flattening — keep transparency as-is

        $this->compressToLimit(
            imagick: $preview,
            maxBytes: config('media.preview.max_size', 1 * 1024 * 1024),
            format: $originalFormat,
        );

        $previewFileName = pathinfo($original->file_name, PATHINFO_FILENAME) . '_preview.' . $originalFormat;
        $tmpPath         = tempnam(sys_get_temp_dir(), 'preview_') . '.' . $originalFormat;

        $preview->writeImage($tmpPath);
        clearstatcache(true, $tmpPath);

        $previewMedia = handleMediaUploads(
            files: new UploadedFile(
                path: $tmpPath,
                originalName: $previewFileName,
                mimeType: $originalMime,
                test: true,
            ),
            modelData: $original->model,
            collectionName: $previewCollection,
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

    private function getMimeType(string $format): string
    {
        return match ($format) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'tiff' => 'image/tiff',
            default => 'image/' . $format,
        };
    }

    private function compressToLimit(Imagick $imagick, int $maxBytes, string $format = 'png'): void
    {
        $quality    = 85;
        $minQuality = 20;
        $step       = 5;

        // Only set quality-based compression for formats that support it
        $supportsQuality = in_array($format, ['webp', 'jpeg', 'jpg']);

        if ($supportsQuality) {
            $imagick->setImageCompressionQuality($quality);

            while ($quality >= $minQuality) {
                $imagick->setImageCompressionQuality($quality);

                if (strlen($imagick->getImageBlob()) <= $maxBytes) {
                    return;
                }

                $quality -= $step;
            }
        }

        // Resize gradually if still too large (works for all formats)
        while (strlen($imagick->getImageBlob()) > $maxBytes) {
            $currentWidth  = $imagick->getImageWidth();
            $currentHeight = $imagick->getImageHeight();

            if ($currentWidth <= 300 || $currentHeight <= 300) {
                break;
            }

            $newWidth  = (int) round($currentWidth * 0.9);
            $newHeight = (int) round($currentHeight * 0.9);

            $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1, true);

            // For PNG reduce compression level instead of quality
            if ($format === 'png') {
                $imagick->setImageCompressionQuality(9); // max PNG compression
            }
        }
    }
}
