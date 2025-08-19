<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleTransfer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'original_sample_id',
        'new_sample_id',
        'from_batch_group_id',
        'to_batch_group_id',
        'from_batch_id',
        'to_batch_id',
        'transferred_weight',
        'transferred_quantity',
        'remaining_weight',
        'remaining_quantity',
        'transfer_reason',
        'transfer_remarks',
        'status',
        'transfer_date',
        'transferred_by'
    ];

    protected $casts = [
        'transferred_weight' => 'decimal:2',
        'remaining_weight' => 'decimal:2',
        'transferred_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'transfer_date' => 'datetime'
    ];

    // Transfer reason constants
    const REASON_RETESTING = 'retesting';
    const REASON_QUALITY_CHECK = 'quality_check';
    const REASON_ADDITIONAL_EVALUATION = 'additional_evaluation';
    const REASON_OTHER = 'other';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the original sample
     */
    public function originalSample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'original_sample_id');
    }

    /**
     * Get the new sample created from transfer
     */
    public function newSample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'new_sample_id');
    }

    /**
     * Get the source batch group
     */
    public function fromBatchGroup(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'from_batch_group_id');
    }

    /**
     * Get the destination batch group
     */
    public function toBatchGroup(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'to_batch_group_id');
    }

    /**
     * Get the user who performed the transfer
     */
    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    /**
     * Get formatted transfer reason
     */
    public function getTransferReasonLabelAttribute(): string
    {
        return match($this->transfer_reason) {
            self::REASON_RETESTING => 'Retesting',
            self::REASON_QUALITY_CHECK => 'Quality Check',
            self::REASON_ADDITIONAL_EVALUATION => 'Additional Evaluation',
            self::REASON_OTHER => 'Other',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary'
        };
    }
}