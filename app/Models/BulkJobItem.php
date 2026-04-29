<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkJobItem extends Model
{
    use HasFactory;

    protected $table = 'bulk_job_items';

    protected $fillable = [
        'bulk_job_id',
        'template_id',
        'color',
        'side',
        'status',
        'output_path',
        'error_message',
        'attempts',
    ];

    protected $casts = [
        'attempts' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const SIDE_FRONT = 'front';
    public const SIDE_BACK = 'back';
    public const SIDE_NONE = 'none';

    public function job(): BelongsTo
    {
        return $this->belongsTo(MockupGenerationJob::class, 'bulk_job_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
    public function getDesignUrl(): string
    {
        return $this->template?->getImageUrlForType($this->side) ?? asset('images/default-product.png');
    }
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markAsCompleted(?string $outputPath = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'output_path' => $outputPath ?? $this->output_path,
            'error_message' => null,
        ]);
    }

    public function markAsFailed(?string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}
