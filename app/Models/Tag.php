<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasTranslations;
    protected $fillable = ['name'];
    public $translatable = ['name'];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class,'taggable');
    }
    public function templates(): MorphToMany
    {
        return $this->morphedByMany(Template::class,'taggable');
    }
}
