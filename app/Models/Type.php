<?php

namespace App\Models;

use App\Enums\Template\TypeEnum;
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
        return $this->morphedByMany(Template::class,'typeable')->withTimestamps();
    }

    public function mockups()
    {
        return $this->morphedByMany(Mockup::class,'typeable')->withTimestamps();
    }
}
