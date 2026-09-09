<?php

namespace App\Models\Concerns;

use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAttachedBundles
{
    public function bundleItems(): MorphMany
    {
        return $this->morphMany(BundleItem::class, 'itemable');
    }
    public function getHasBundlesAttribute(): bool
    {
        if ($this->relationLoaded('attachedBundles')) {
            return $this->attachedBundles->isNotEmpty();
        }

        return false;
    }
    public function activeAttachedBundles()
    {
        return Bundle::query()
            ->with([
                'trigger.itemable',
                'rewards.itemable',
            ])
            ->where('status', 'active')
            ->whereHas('items', function ($query) {
                $query
                    ->where('itemable_type', $this->getMorphClass())
                    ->where('itemable_id', $this->id);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->latest()
            ->get()
            ->unique('id')
            ->values();
    }
}
