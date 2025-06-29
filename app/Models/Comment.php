<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{MorphMany, MorphTo};


class Comment extends Model
{
    protected $fillable = [
        'body',
        'commentable_id',
        'commentable_type',
        'owner_id',
        'owner_type',
        'parent_id',
        'parent_type',
        'position_x',
        'position_y',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): MorphMany
    {
        return $this->morphMany(Comment::class, 'parent');
    }

    public function parent(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'parent_type', 'parent_id');
    }
}
