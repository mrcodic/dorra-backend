<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable =[
        'product_id',
        'user_id',
        'rating',
        'comment',
        'comment_at',
        'review',
    ];

    protected function casts()
    {
        return [
            'comment_at' => 'datetime',
        ];
    }

    protected function images(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $this->getMedia('reviews')
        );
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
