<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockupGenerationJob extends Model
{
    use HasFactory;

    protected $table = 'mockup_generation_jobs';

    protected $fillable = [
        'mockup_id',
        'status',
        'total_count',
        'completed_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_count' => 'integer',
        'completed_count' => 'integer',
        'failed_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function mockup(): BelongsTo
    {
        return $this->belongsTo(Mockup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BulkJobItem::class, 'bulk_job_id');
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    public function incrementCompleted(): void
    {
        $this->increment('completed_count');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }
}
