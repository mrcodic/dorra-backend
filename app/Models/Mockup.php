<?php

namespace App\Models;

use App\Enums\Mockup\TypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'colors',
        'area_top',
        'area_left',
        'area_height',
        'area_width',
    ];
    protected $appends = [
        'front_base_image_url',
        'front_mask_image_url',
        'back_base_image_url',
        'back_mask_image_url',
        'none_base_image_url',
        'none_mask_image_url',
    ];

    protected $casts = [
        'colors' => 'array',
        'type' => TypeEnum::class,
    ];
    protected $attributes = [
        'area_width' => 200,
        'area_left' => 233,
        'area_top' => 233,
        'area_height' => 370,
    ];
    protected function getSideMediaUrl(string $side, string $role)
    {
        $media = $this->getMedia('mockups')->first(function ($media) use ($side, $role) {
            return $media->getCustomProperty('side') === $side
                && $media->getCustomProperty('role') === $role;
        });

        return $media?->getFullUrl();
    }

    public function getFrontBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('front', 'base');
    }

    public function getFrontMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('front', 'mask');
    }

    public function getBackBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('back', 'base');
    }

    public function getBackMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('back', 'mask');
    }

    public function getNoneBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('none', 'base');
    }

    public function getNoneMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('none', 'mask');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function types()
    {
        return $this->morphToMany(Type::class, 'typeable')
            ->using(Typeable::class)
            ->withTimestamps();
    }

    public function templates()
    {
        return $this->belongsToMany(
            Template::class,
            'mockup_template',
            'mockup_id',
            'template_id'
        )
            ->using(MockupTemplate::class)
            ->withPivot(['id','positions','colors'])
            ->withTimestamps();
    }
}
