<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name' ,
        'print_x' ,
        'print_y' ,
        'print_height' ,
        'print_width' ,
    ];
}
