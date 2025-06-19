<?php

namespace App\Models;

use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    protected $fillable = ['name', 'country_id'];

public function country()
{
    return $this->belongsTo(Country::class, 'country_id');
}

}
