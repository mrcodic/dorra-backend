<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'reviewable_id',
        'reviewable_type',
        'user_id',
        'rating',
        'comment',
        'comment_at',
        'review',
    ];
    protected $attributes = [
        'reviewable_type' => Product::class,
    ];

    public function reviewable(): MorphTo
    {
        return $this->morphTo('reviewable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
}
