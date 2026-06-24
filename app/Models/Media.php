<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
