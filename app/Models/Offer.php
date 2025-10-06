<?php

namespace App\Models;

use App\Enums\Offer\TypeEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Offer extends Model
{
    use HasTranslations;
    public $translatable = ['name'];
    protected $fillable = [
        'name',
        'value',
        'type',
        'start_at',
        'end_at',
    ];
    protected $casts = [
        'type' => TypeEnum::class,
    ];
}
