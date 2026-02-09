<?php

namespace App\Models;

use App\Enums\CreditOrder\StatusEnum;
use Illuminate\Database\Eloquent\Model;

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

    protected function casts()
    {
        return [
            'status' => StatusEnum::class,
        ];
    }
    protected static function booted()
    {
        self::created(function ($model) {
            $model->number = sprintf('%s-%s-%06d',"#CRORD", now()->format('Ymd'), $model->id);
            $model->saveQuietly();
        });
        parent::booted();
    }
}
