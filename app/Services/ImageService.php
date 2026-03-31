<?php

namespace App\Services;

use Imagick;
use ImagickPixel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageService
{
    /**
     * Called on save — receives the media ID from the Dropzone upload.
     * Calculates metadata for the original, generates + stores the preview,
     * returns both IDs to attach to the design record.
     */
    public function processUploaded(int $mediaId, string $collectionName = 'templates'): array
    {
        $original = Media::findOrFail($mediaId);

        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        $imagick = new Imagick($filePath);

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

        $previewMedia = $this->storePreview($imagick, $original, $collectionName . '-preview');

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

    private function storePreview(Imagick $imagick, Media $original, string $previewCollection): Media
    {
        $preview = clone $imagick;

        $originalWidth  = $imagick->getImageWidth();
        $originalHeight = $imagick->getImageHeight();
        $maxWidth       = config('media.preview.max_width');
        $maxHeight      = config('media.preview.max_height');

        if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
            $preview->thumbnailImage($maxWidth, $maxHeight, bestfit: true);
        }

        $preview->setImageCompressionQuality(config('media.preview.quality'));
        $preview->stripImage();

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
        $previewMedia->update([
            'model_type' => $original->model_type,
            'model_id'   => $original->model_id,
        ]);
        $preview->destroy();
        @unlink($tmpPath);

        return $previewMedia;
    }
}
