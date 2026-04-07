<?php

namespace App\Models;

use App\Enums\DiscountCode\ScopeEnum;
use App\Enums\DiscountCode\TypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_usage',
        'used',
        'expired_at',
        'scope',
        'code_mode',
        'show_for_new_registered_users',
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'discountable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'discountable');
    }

    public function value(): Attribute
    {
        return Attribute::get(function ($value) {
            if ($this->type == TypeEnum::PERCENTAGE) {
                return $value / 100;
            }
            return $value;
        });
    }
    public function scopeIsNotValid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($q){
                $q->whereNotNull('expired_at')
                    ->where('expired_at', '<=', now());
            })
                ->orWhere(function ($q) {
                    $q->whereNotNull('max_usage')
                        ->whereColumn('used', 'max_usage');
                });
        });
    }


    protected function casts(): array
    {
        return [
            'type' => TypeEnum::class,
            'scope' => ScopeEnum::class,
        ];
    }

}
