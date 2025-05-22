<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;

class Role extends SpatieRole
{
    use HasTranslations;
    public $translatable = ['name', 'description',];
    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

}
