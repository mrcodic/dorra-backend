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
            ->withPivot(['id','positions'])
            ->withTimestamps();
    }
}
