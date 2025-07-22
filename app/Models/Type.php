<?php

namespace App\Models;

use App\Enums\Product\TypeEnum;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable =[
        'value',
    ];
    protected $casts =[
        'value' => TypeEnum::class,
    ];

    public function templates()
    {
        return $this->belongsToMany(Template::class)->withTimestamps();
    }
}
