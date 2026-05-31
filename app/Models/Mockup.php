<?php

namespace App\Models;

use App\Enums\Mockup\TypeEnum;
use App\Observers\MockupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
#[ObservedBy(MockupObserver::class)]
class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'colors',
        'area_top',
        'area_left',
        'area_height',
        'area_width',
        'approach',
        'fill_ratio',
        'light_strength',
        'shadow_strength',
        'displacement_scale',
        'pre_fill_colors',
    ];
    protected $appends = [
        'front_base_image_url',
        'front_mask_image_url',
        'front_shadow_image_url',
        'back_base_image_url',
        'back_mask_image_url',
        'back_shadow_image_url',
        'none_base_image_url',
        'none_mask_image_url',
        'none_shadow_image_url',
    ];

    protected $casts = [
        'colors' => 'array',
        'pre_fill_colors' => 'array',
        'type' => TypeEnum::class,
    ];
    protected $attributes = [
        'area_width' => 200,
        'area_left' => 233,
        'area_top' => 233,
        'area_height' => 370,
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    public function getFrontBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('front', 'base');
    }

    protected function getSideMediaUrl(string $side, string $role)
    {
        $media = $this->getMedia('mockups')->first(function ($media) use ($side, $role) {
            return $media->getCustomProperty('side') === $side
                && $media->getCustomProperty('role') === $role;
        });

        return $media?->getFullUrl();
    }

    public function getFrontMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('front', 'mask');
    }

    public function getFrontShadowImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('front', 'shadow');
    }

    public function getBackBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('back', 'base');
    }

    public function getBackMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('back', 'mask');
    }

    public function getBackShadowImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('back', 'shadow');
    }

    public function getNoneBaseImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('none', 'base');
    }

    public function getNoneMaskImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('none', 'mask');
    }

    public function getNoneShadowImageUrlAttribute(): ?string
    {
        return $this->getSideMediaUrl('none', 'shadow');
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

    public function sideSettings(): HasMany
    {
        return $this->hasMany(MockupSideSetting::class);
    }

    protected function templateColors(): Attribute
    {
        return Attribute::get(function () {
            $templateId = request('template_id');

            $templates = $this->relationLoaded('templates')
                ? $this->templates
                : $this->templates()->withPivot(['colors', 'positions'])->get();

            if ($templateId) {
                $templates = $templates->where('id', (int)$templateId);
            }

            return $templates
                ->flatMap(function ($tpl) {

                    $colors = $tpl->pivot->colors ?? [];

                    if (is_string($colors)) {
                        $colors = json_decode($colors, true) ?: [];
                    }
                    if (!is_array($colors)) {
                        $colors = [];
                    }

                    return collect($colors)
                        ->filter(fn($c) => is_string($c))
                        ->map(fn($c) => strtoupper(trim($c)))
                        ->filter(fn($c) => preg_match('/^#([A-F0-9]{3}|[A-F0-9]{6})$/', $c));
                })
                ->unique()
                ->values()
                ->all();
        });
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
            ->withPivot(['id', 'positions', 'colors', 'model_color','type'])
            ->withTimestamps();
    }

    protected function baseImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                foreach ([['front', 'base'], ['back', 'base'], ['none', 'base']] as [$side, $role]) {
                    $url = $this->media()
                        ->where('collection_name', 'mockups')
                        ->where('custom_properties->side', $side)
                        ->where('custom_properties->role', $role)
                        ->value('file_name');

                    if ($url) {

                        return $this->media()
                            ->where('collection_name', 'mockups')
                            ->where('custom_properties->side', $side)
                            ->where('custom_properties->role', $role)
                            ->first()
                            ->getFullUrl();
                    }
                }

                return null;
            }
        );
    }
}
