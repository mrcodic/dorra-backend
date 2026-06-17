<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class TableauScene extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'sceneable_id',
        'sceneable_type',
        'positions',
        'name',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'positions'=>'array',
    ];

    public function sceneable(): MorphTo
    {
        return $this->morphTo();
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(Template::class)
            ->using(TableauSceneTemplate::class)
            ->withPivot('positions')
            ->withTimestamps();
    }
    public function image()
    {
        return $this->getFirstMedia('tableau_scene_image');
    }

    public function imageUrl(): ?string
    {
        return $this->getFirstMediaUrl('tableau_scene_image') ?: null;
    }
}
