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
    public function processUploaded(int $mediaId): array
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

        $previewMedia = $this->storePreview($imagick, $original);

        $imagick->destroy();

        return [
            'original_media_id' => $original->id,
            'preview_media_id'  => $previewMedia->id,
        ];
    }

    private function storePreview(Imagick $imagick, Media $original): Media
    {
        $preview = clone $imagick;

        // Resize — cap longest side at 1200px, keep aspect ratio
        $preview->thumbnailImage(1200, 1200, bestfit: true);
        $preview->setImageCompressionQuality(80);
        $preview->stripImage();
        // Preserve alpha if original had it
        if ($preview->getImageAlphaChannel()) {
            $preview->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $preview->setBackgroundColor(new ImagickPixel('transparent'));
        }

        // Keep same format as original
        $ext         = pathinfo($original->file_name, PATHINFO_EXTENSION);
        $tmpPath     = tempnam(sys_get_temp_dir(), 'preview_') . '.' . $ext;
        $previewFileName = pathinfo($original->file_name, PATHINFO_FILENAME) . '_preview.' . $ext;

        $preview->writeImage($tmpPath);

        $previewMedia = handleMediaUploads(
            files           : new UploadedFile(
                path        : $tmpPath,
                originalName: $previewFileName,
                mimeType    : $original->mime_type, // same mime as original
                test        : true,
            ),
            collectionName  : $original->collection_name.'-preview',
            customProperties: [
                'width'       => $preview->getImageWidth(),
                'height'      => $preview->getImageHeight(),
                'has_alpha'   => (bool) $preview->getImageAlphaChannel(),
                'original_id' => $original->id,
            ],
        );
        
        $previewMedia->update([
            'model_type' => get_class($original),
            'model_id' => $original->id
        ]);

        $preview->destroy();
        @unlink($tmpPath);

        return $previewMedia;
    }
}
