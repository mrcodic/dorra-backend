<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    protected static function booted()
    {
        static::deleting(function ($media) {
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
        });

        parent::booted();
    }

    public function templates(): MorphToMany
    {
        return $this->morphedByMany(
            Template::class,
            'mediable'
        );
    }
    public function fonts(): MorphToMany
    {
        return $this->morphedByMany(
            Template::class,
            'mediable'
        )->whereType('font');
    }
    public function designs(): MorphToMany
    {
        return $this->morphedByMany(
            Design::class,
            'mediable'
        );
    }
}
