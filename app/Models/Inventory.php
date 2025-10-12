<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Inventory extends Model
{
    protected $fillable = ['name', 'number','parent_id','is_available'];

    public function scopeAvailable()
    {
        return $this->whereIsAvailable(true);
    }
    public function scopeUnAvailable()
    {
        return $this->whereIsAvailable(false);
    }
}
