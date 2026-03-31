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

        // Remove metadata
        $preview->stripImage();

        // Force preview format to JPEG for better compression
        $preview->setImageFormat('jpeg');
        $preview->setImageCompression(Imagick::COMPRESSION_JPEG);
        $preview->setImageCompressionQuality(85);
        $preview->setInterlaceScheme(Imagick::INTERLACE_JPEG);

        // Flatten transparency if exists, because JPEG does not support alpha
        if ($preview->getImageAlphaChannel()) {
            $background = new Imagick();
            $background->newImage(
                $preview->getImageWidth(),
                $preview->getImageHeight(),
                new ImagickPixel('white')
            );
            $background->setImageFormat('jpeg');
            $background->compositeImage($preview, Imagick::COMPOSITE_OVER, 0, 0);
            $preview->destroy();
            $preview = $background;
        }

        $this->compressToLimit(
            imagick: $preview,
            maxBytes: config('media.preview.max_size', 1 * 1024 * 1024),
        );

        $tmpPath = tempnam(sys_get_temp_dir(), 'preview_') . '.jpg';
        $previewFileName = pathinfo($original->file_name, PATHINFO_FILENAME) . '_preview.jpg';

        $preview->writeImage($tmpPath);

        clearstatcache(true, $tmpPath);

        $previewMedia = handleMediaUploads(
            files: new UploadedFile(
                path: $tmpPath,
                originalName: $previewFileName,
                mimeType: 'image/jpeg',
                test: true,
            ),
            modelData: $original->model,
            collectionName: $previewCollection,
            customProperties: [
                'width' => $preview->getImageWidth(),
                'height' => $preview->getImageHeight(),
                'has_alpha' => false,
                'original_id' => $original->id,
            ],
        );

        $preview->destroy();
        @unlink($tmpPath);

        return $previewMedia;
    }
    private function compressToLimit(Imagick $imagick, int $maxBytes): void
    {
        $quality = 85;
        $minQuality = 20;
        $step = 5;

        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality($quality);

        // First try quality reduction
        while ($quality >= $minQuality) {
            $imagick->setImageCompressionQuality($quality);

            if (strlen($imagick->getImageBlob()) <= $maxBytes) {
                return;
            }

            $quality -= $step;
        }

        // If still too large, resize gradually
        while (strlen($imagick->getImageBlob()) > $maxBytes) {
            $currentWidth = $imagick->getImageWidth();
            $currentHeight = $imagick->getImageHeight();

            // prevent endless loop on very small images
            if ($currentWidth <= 300 || $currentHeight <= 300) {
                break;
            }

            $newWidth = (int) round($currentWidth * 0.9);
            $newHeight = (int) round($currentHeight * 0.9);

            $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1, true);
            $imagick->setImageCompressionQuality($minQuality);
        }
    }
}
