<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraCredit extends Model
{
    protected $fillable = ['user_id', 'admin_id', 'amount'];
}
