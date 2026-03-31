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

        // [0] = first frame only — avoids Invalid image geometry on multi-frame files
        $imagick = new Imagick($filePath . '[0]');

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
        // ✅ Re-open from file instead of clone — more reliable across Imagick versions
        $filePath = Storage::disk($original->disk)
            ->path("{$original->id}/{$original->file_name}");

        $preview = new Imagick($filePath . '[0]');

        $originalWidth  = $preview->getImageWidth();
        $originalHeight = $preview->getImageHeight();
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
