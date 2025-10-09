<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    protected $fillable = ['inventoryable_id', 'inventoryable_type', 'blocked_qty', 'shipped_qty'];

    protected $appends = ['total_qty', 'available_qty'];

    public function inventoryable()
    {
        return $this->morphTo();
    }

    public function getTotalQtyAttribute(): int
    {
        if ($this->inventoryable_type === Product::class) {
            return OrderItem::where('orderable_id', $this->inventoryable_id)
                ->where('orderable_type' , Product::class)
                ->sum('quantity');
        }
        if ($this->inventoryable_type === Category::class)
        {
            return OrderItem::where('orderable_id', $this->inventoryable_id)
                ->where('orderable_type' , Category::class)
                ->sum('quantity');
        }

        return 0;
    }

    public function getAvailableQtyAttribute(): int
    {
        return max(0, $this->total_qty - $this->blocked_qty - $this->shipped_qty);
    }

    public function reserve(int $qty): void
    {
        $this->increment('blocked_qty', $qty);
    }

    public function ship(int $qty): void
    {
        DB::transaction(function () use ($qty) {
            $this->decrement('blocked_qty', min($this->blocked_qty, $qty));
            $this->increment('shipped_qty', $qty);
        });
    }
}
