<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TableauSceneTemplate extends Pivot
{
    protected $guarded=[];
    protected $casts = [
        'positions' => 'array'
    ];
}
