<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LandingReview extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable =[
        'customer',
        'rate',
        'date',
        'review',
        'type'
    ];
}
