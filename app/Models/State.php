<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class State extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'country_id'];
    public $translatable = ['name'];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}
