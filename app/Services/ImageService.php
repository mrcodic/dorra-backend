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

        \Log::debug('Imagick info', [
            'width'      => $imagick->getImageWidth(),
            'height'     => $imagick->getImageHeight(),
            'format'     => $imagick->getImageFormat(),
            'depth'      => $imagick->getImageDepth(),
            'colorspace' => $imagick->getImageColorspace(),
            'file_path'  => $filePath,
            'file_size'  => filesize($filePath),
        ]);

        $original->update([
            'custom_properties' => array_merge(
                $original->custom_properties ?? [],
                [
                    'width'     => $imagick->getImageWidth(),
                    'height'    => $imagick->getImageHeight(),
                    'has_alpha' => (bool) $imagick->getImageAlphaChannel(),
                ]
            ),
        ]);

        $previewMedia = $this->storePreview($original, $collectionName . '-preview');

        // Link preview ID onto original
        $original->update([
            'custom_properties' => array_merge(
                $original->custom_properties ?? [],
                ['preview_id' => $previewMedia->id]
            ),
        ]);

        $imagick->destroy();

        return [
            'original_media_id' => $original->id,
            'preview_media_id'  => $previewMedia->id,
        ];
    }

    private function storePreview(Media $original, string $previewCollection): Media
    {
        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        // Re-open from file — more reliable than clone across Imagick versions
        $preview = new Imagick($filePath . '[0]');

        $originalWidth  = $preview->getImageWidth();
        $originalHeight = $preview->getImageHeight();
        $maxWidth       = config('media.preview.max_width');
        $maxHeight      = config('media.preview.max_height');

        \Log::debug('Before thumbnail', [
            'width'  => $originalWidth,
            'height' => $originalHeight,
            'format' => $preview->getImageFormat(),
        ]);

        // Only downscale — never upscale
        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $preview->thumbnailImage($maxWidth, $maxHeight, bestfit: true);
        }

        // Strip AFTER resize — stripping before can cause geometry loss
        $preview->stripImage();
        $preview->setImageCompressionQuality(config('media.preview.quality'));

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
            collectionName  : $previewCollection,
            customProperties: [
                'width'       => $preview->getImageWidth(),
                'height'      => $preview->getImageHeight(),
                'has_alpha'   => (bool) $preview->getImageAlphaChannel(),
                'original_id' => $original->id,
            ],
        );

        // Inherit model attachment from original
        $previewMedia->update([
            'model_type' => $original->model_type,
            'model_id'   => $original->model_id,
        ]);

        $preview->destroy();
        @unlink($tmpPath);

        return $previewMedia;
    }
}
