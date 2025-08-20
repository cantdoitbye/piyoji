<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_id',
        'batch_group_id',
        'batch_id',
        'allocated_weight',
        'allocation_type',
        'allocation_reason',
        'allocation_date',
        'allocated_by',
        'status',
        'remarks'
    ];

    protected $casts = [
        'allocated_weight' => 'decimal:2',
        'allocation_date' => 'datetime'
    ];

    // Allocation type constants
    const TYPE_BATCH_TESTING = 'batch_testing';
    const TYPE_RETESTING = 'retesting';
    const TYPE_QUALITY_CHECK = 'quality_check';
    const TYPE_ADDITIONAL_EVALUATION = 'additional_evaluation';

    // Status constants
    const STATUS_ALLOCATED = 'allocated';
    const STATUS_USED = 'used';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    // Fixed allocation weight (10gm)
    const FIXED_ALLOCATION_WEIGHT = 0.01; // 10gm in kg

    /**
     * Get the sample this allocation belongs to
     */
    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    /**
     * Get the batch group this allocation is for
     */
    public function batchGroup(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'batch_group_id');
    }

    /**
     * Get the user who made the allocation
     */
    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    /**
     * Get formatted allocation type
     */
    public function getAllocationTypeLabelAttribute(): string
    {
        return match($this->allocation_type) {
            self::TYPE_BATCH_TESTING => 'Batch Testing',
            self::TYPE_RETESTING => 'Retesting',
            self::TYPE_QUALITY_CHECK => 'Quality Check',
            self::TYPE_ADDITIONAL_EVALUATION => 'Additional Evaluation',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ALLOCATED => 'Allocated',
            self::STATUS_USED => 'Used',
            self::STATUS_RETURNED => 'Returned',
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
            self::STATUS_ALLOCATED => 'bg-info',
            self::STATUS_USED => 'bg-success',
            self::STATUS_RETURNED => 'bg-warning',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Scope for active allocations
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ALLOCATED, self::STATUS_USED]);
    }

    /**
     * Scope for allocations by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('allocation_type', $type);
    }

    /**
     * Scope for allocations by date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('allocation_date', [$startDate, $endDate]);
    }
}