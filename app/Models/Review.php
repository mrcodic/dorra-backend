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
    use Illuminate\Database\Eloquent\Builder;

    public function scopeWhereRoundedAvgRatingIn(Builder $query, array|string $ratings): Builder
    {
        // normalize: accept "3,4" or [3,4]
        $ratings = is_array($ratings) ? $ratings : explode(',', (string) $ratings);
        $ratings = array_values(array_filter(array_map('intval', $ratings)));

        if (empty($ratings)) {
            return $query;
        }

        $placeholders     = implode(',', array_fill(0, count($ratings), '?'));
        $reviewableType   = $query->getModel()->getMorphClass(); // morph type for this model
        $table            = $query->getModel()->getTable();

        return $query->whereIn("$table.id", function ($sub) use ($reviewableType, $ratings, $placeholders) {
            $sub->from('reviews')
                ->select('reviewable_id')
                ->where('reviewable_type', $reviewableType)
                ->groupBy('reviewable_id')
                ->havingRaw("ROUND(AVG(rating)) IN ($placeholders)", $ratings);
        });
    }

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
