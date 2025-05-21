<?php

namespace App\Models;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
    protected $fillable =[
        'code',
        'type',
        'value',
        'max_usage',
        'used',
        'expired_at',
        'scope',
    ];

    protected static function booted()
    {
        static::creating(function ($discountCode) {
            $discountCode->code = $discountCode->code.Str::random(16);
        });
        parent::booted();
    }

    protected function casts(): array
    {
        return [
            'type' => TypeEnum::class,
            'scope' => ScopeEnum::class,
        ];
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'discountable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'discountable');
    }
}
