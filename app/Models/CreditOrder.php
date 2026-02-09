<?php

namespace App\Models;

use App\Enums\CreditOrder\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditOrder extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'credits',
        'amount',
        'status',
        'plan_id'
    ];

    protected static function booted(): void
    {
        self::created(function ($model) {
            $model->number = sprintf('%s-%s-%06d', "#CR-ORD", now()->format('Ymd'), $model->id);
            $model->saveQuietly();
        });
        parent::booted();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'status' => StatusEnum::class,
        ];
    }
}
