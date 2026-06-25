<?php

namespace App\Observers;

use App\Models\Media;
use App\Models\Mockup;
use Illuminate\Support\Facades\Storage;

class MediaObserver extends \Spatie\MediaLibrary\MediaCollections\Models\Observers\MediaObserver
{
    public function created(Media $media): void
    {
        if ($media->collection_name !== 'generated_mockups') {
            return;
        }

        $templateId = $media->getCustomProperty('template_id');
        $hex = $media->getCustomProperty('hex');

        if (!$templateId || !$hex) {
            return;
        }

        if ($media->model_type !== Mockup::class) {
            return;
        }

        /** @var Mockup|null $mockup */
        $mockup = Mockup::find($media->model_id);

        if (!$mockup) {
            return;
        }

        $normalizedHex = strtolower(ltrim($hex, '#'));

        // Check if this mockup's pivot model_color matches the media's hex
        $matched = $mockup->templates()
            ->wherePivot('template_id', $templateId)
            ->whereRaw(
                "LOWER(TRIM(LEADING '#' FROM mockup_template.model_color)) = ?",
                [$normalizedHex]
            )
            ->exists();

        if (!$matched) {
            return;
        }

        // Set model_image = 1 in custom_properties of this media
        $media->setCustomProperty('model_image', 1);
        $media->saveQuietly(); // avoid re-triggering observer events
    }

    public function deleting(Media $media)
    {
        $previewId = $media->getCustomProperty('preview_id');

        if (!$previewId) {
            return;
        }

        $preview = Media::find($previewId);

        if ($preview && $preview->id !== $media->id) {

            Storage::disk($preview->disk)->delete($preview->getPathRelativeToRoot());

            foreach ($preview->generated_conversions ?? [] as $conversion => $generated) {
                if ($generated) {
                    Storage::disk($preview->conversions_disk ?? $preview->disk)
                        ->delete($preview->getPath($conversion));
                }
            }

            $preview->delete();
        }
    }

}
