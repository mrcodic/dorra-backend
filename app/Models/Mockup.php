<?php

namespace App\Models;

use App\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Mockup extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'type',
        'product_id',
    ];



    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
