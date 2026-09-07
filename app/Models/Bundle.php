<?php

namespace App\Models;

use App\Enums\Bundle\RepeatTypeEnum;
use App\Enums\Bundle\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Bundle extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'description',
        'status',
        'application_type',
        'repeat_type',
        'auto_add_ready_rewards',
        'show_on_website',
        'show_on_product_page',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusEnum::class,
            'repeat_type' => RepeatTypeEnum::class,
            'auto_add_ready_rewards' => 'boolean',
            'show_on_website' => 'boolean',
            'show_on_product_page' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class)->orderBy('sort_order');
    }

    public function trigger(): HasOne
    {
        return $this->hasOne(BundleItem::class)
            ->where('role', 'trigger');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(BundleItem::class)
            ->where('role', 'reward')
            ->orderBy('sort_order');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', StatusEnum::ACTIVE->value)
            ->where(function (Builder $query) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === StatusEnum::DRAFT) {
            return 'Draft';
        }

        if ($this->start_at?->isFuture()) {
            return 'Scheduled';
        }

        if ($this->end_at?->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }

    protected static function booted(): void
    {
        static::deleting(function (Bundle $bundle) {
            if (! $bundle->isForceDeleting()) {
                $bundle->items()->get()->each->delete();
            }
        });
    }
}
