<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable =[
        'team_id',
        'email',
        'token',
        'accepted_at',
    ];

}
