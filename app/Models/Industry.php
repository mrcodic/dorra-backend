<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Industry extends Model
{
    use HasTranslations,SoftDeletes;

    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'parent_id',
    ];
    public function templates(): MorphToMany
    {
        return $this->morphedByMany(Template::class,'industryable','industryables');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Industry::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Industry::class, 'parent_id');
    }

}
