<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Sample extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sample_id',
        'sample_name',
        'seller_id',
        'batch_id',
        'weight_per_sample',
                'number_of_samples',
        'sample_weight',
        'arrival_date',
        'received_by',
        'status',
        'remarks',
                'batch_group_id',
        // Evaluation fields
        'aroma_score',
        'liquor_score',
        'appearance_score',
        'overall_score',
        'color_score',
        'taste_score',
        'strength_score',
        'briskness_score',
        'evaluation_comments',
        'evaluation_status',
           'catalog_weight',
    'allocated_weight',
    'available_weight',
    'allocation_count',
    'has_sufficient_weight',
        'evaluated_by',
        'evaluated_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'evaluated_at' => 'datetime',
                'number_of_samples' => 'integer',
        'sample_weight' => 'decimal:2',
                'weight_per_sample' => 'decimal:2',
        'aroma_score' => 'decimal:1',
        'liquor_score' => 'decimal:1',
        'appearance_score' => 'decimal:1',
        'overall_score' => 'decimal:1',
         'catalog_weight' => 'decimal:2',
    'allocated_weight' => 'decimal:2',
    'available_weight' => 'decimal:2',
    'allocation_count' => 'integer',
    'has_sufficient_weight' => 'boolean'
    ];

    protected $dates = [
        'arrival_date',
        'evaluated_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Status constants
    const STATUS_RECEIVED = 'received';
    const STATUS_PENDING_EVALUATION = 'pending_evaluation';
    const STATUS_EVALUATED = 'evaluated';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ASSIGNED_TO_BUYERS = 'assigned_to_buyers';

    // Evaluation status constants
    const EVALUATION_PENDING = 'pending';
    const EVALUATION_IN_PROGRESS = 'in_progress';
    const EVALUATION_COMPLETED = 'completed';
    const FIXED_ALLOCATION_WEIGHT = 0.01; 


    /**
     * Get the seller that owns the sample.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

      /**
     * Get the batch group this sample belongs to
     */
    public function batchGroup(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'batch_group_id');
    }

    /**
     * Get the user who received the sample.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who evaluated the sample.
     */
    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Get the user who created the sample record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the sample record.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate unique sample ID
     */
    public static function generateSampleId(): string
    {
        $prefix = 'SMP';
        $year = date('Y');
        $month = date('m');
        
        // Get the last sample ID for current month
        $lastSample = self::where('sample_id', 'like', $prefix . $year . $month . '%')
            ->orderBy('sample_id', 'desc')
            ->first();
        
        if ($lastSample) {
            $lastNumber = (int) substr($lastSample->sample_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if sample is evaluated
     */
    public function isEvaluated(): bool
    {
        return $this->evaluation_status === self::EVALUATION_COMPLETED;
    }

    /**
     * Check if sample is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if sample is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_PENDING_EVALUATION => 'Pending Evaluation',
            self::STATUS_EVALUATED => 'Evaluated',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_ASSIGNED_TO_BUYERS => 'Assigned to Buyers',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_RECEIVED => 'bg-info',
            self::STATUS_PENDING_EVALUATION => 'bg-warning',
            self::STATUS_EVALUATED => 'bg-primary',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_ASSIGNED_TO_BUYERS => 'bg-secondary',
            default => 'bg-light'
        };
    }

    /**
     * Get evaluation status label
     */
    public function getEvaluationStatusLabelAttribute(): string
    {
        return match($this->evaluation_status) {
            self::EVALUATION_PENDING => 'Pending',
            self::EVALUATION_IN_PROGRESS => 'In Progress',
            self::EVALUATION_COMPLETED => 'Completed',
            default => 'Not Started'
        };
    }

    /**
     * Calculate overall score from individual scores
     */
    public function calculateOverallScore(): float
    {
        if (!$this->aroma_score || !$this->liquor_score || !$this->appearance_score) {
            return 0;
        }
        
        // Average of all three scores (assuming equal weightage)
        return round(($this->aroma_score + $this->liquor_score + $this->appearance_score) / 3, 1);
    }

    /**
     * Auto-calculate overall score before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($sample) {
            if ($sample->aroma_score && $sample->liquor_score && $sample->appearance_score) {
                $sample->overall_score = $sample->calculateOverallScore();
            }
        });

        static::creating(function ($sample) {
            if (!$sample->sample_id) {
                $sample->sample_id = self::generateSampleId();
            }
        });

          static::saving(function ($sample) {
            // Calculate total weight if weight_per_sample and number_of_samples are set
            if ($sample->weight_per_sample && $sample->number_of_samples) {
                $sample->sample_weight = $sample->weight_per_sample * $sample->number_of_samples;
            }
        });

         static::creating(function ($sample) {
        $sample->catalog_weight = $sample->sample_weight;
        $sample->available_weight = $sample->sample_weight;
        $sample->allocated_weight = 0;
        $sample->allocation_count = 0;
        $sample->has_sufficient_weight = $sample->sample_weight >= self::FIXED_ALLOCATION_WEIGHT;
    });

    static::updating(function ($sample) {
        if ($sample->isDirty('sample_weight')) {
            $sample->catalog_weight = $sample->sample_weight;
            $sample->available_weight = $sample->sample_weight - $sample->allocated_weight;
            $sample->has_sufficient_weight = $sample->available_weight >= self::FIXED_ALLOCATION_WEIGHT;
        }
    });
    }

    /**
     * Scope for active samples
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope for samples by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for evaluated samples
     */
    public function scopeEvaluated($query)
    {
        return $query->where('evaluation_status', self::EVALUATION_COMPLETED);
    }

    /**
     * Scope for approved samples
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for top scoring samples
     */
    public function scopeTopScoring($query, $minScore = 8.0)
    {
        return $query->where('overall_score', '>=', $minScore)
            ->orderBy('overall_score', 'desc');
    }


  
/**
 * Get buyer assignments for this sample (Module 2.3)
 */
public function buyerAssignments()
{
    return $this->hasMany(SampleBuyerAssignment::class);
}



/**
 * Get buyers assigned to this sample
 */
public function assignedBuyers()
{
    return $this->belongsToMany(Buyer::class, 'sample_buyer_assignments')
                ->withPivot(['assignment_remarks', 'dispatch_status', 'assigned_at', 'assigned_by', 'dispatched_at', 'tracking_id'])
                ->withTimestamps();
}

/**
 * Check if sample is assigned to any buyers
 */
public function isAssignedToBuyers()
{
    return $this->buyerAssignments()->exists();
}

/**
 * Get count of buyers assigned to this sample
 */
public function getAssignedBuyersCountAttribute()
{
    return $this->buyerAssignments()->count();
}

/**
 * Scope: Samples that are ready for buyer assignment (approved)
 */
public function scopeReadyForBuyerAssignment($query)
{
    return $query->where('status', self::STATUS_APPROVED)
                 ->where('evaluation_status', self::EVALUATION_COMPLETED);
}

/**
 * Scope: Samples already assigned to buyers
 */
public function scopeAssignedToBuyers($query)
{
    return $query->where('status', self::STATUS_ASSIGNED_TO_BUYERS);
}

 /**
     * Check if sample is batched
     */
    public function isBatched(): bool
    {
        return !is_null($this->batch_group_id);
    }

    
   /**
     * Get batch status information
     */
    public function getBatchStatusAttribute(): string
    {
        if ($this->batch_group_id) {
            return 'Batched (' . $this->batch_id . ')';
        }
        return 'Not Batched';
    }

    /**
     * Get total sample quantity (number_of_samples for display)
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->number_of_samples ?? 1;
    }

     public function scopeUnbatched($query)
    {
        return $query->whereNull('batch_group_id');
    }

      public function scopeBatched($query)
    {
        return $query->whereNotNull('batch_group_id');
    }

      public function scopeForDate($query, $date)
    {
        return $query->whereDate('arrival_date', $date);
    }

/**
 * Get transfers FROM this sample (when this sample was the source)
 */
public function transfersFrom()
{
    return $this->hasMany(SampleTransfer::class, 'original_sample_id');
}

/**
 * Get transfers TO this sample (when this sample was created from transfer)
 */
public function transfersTo()
{
    return $this->hasMany(SampleTransfer::class, 'new_sample_id');
}

/**
 * Check if this sample has been transferred
 */
public function hasTransfers(): bool
{
    return $this->transfersFrom()->exists() || $this->transfersTo()->exists();
}

/**
 * Check if this sample was created from a transfer
 */
public function isFromTransfer(): bool
{
    return $this->transfersTo()->exists();
}

/**
 * Get the original sample if this was created from transfer
 */
public function getOriginalSampleAttribute()
{
    $transfer = $this->transfersTo()->first();
    return $transfer ? $transfer->originalSample : null;
}

/**
 * Check if sample can be transferred
 */
// public function canBeTransferred(): bool
// {
//     return $this->batch_group_id && 
//            $this->evaluation_status === self::EVALUATION_COMPLETED &&
//            $this->sample_weight > 0.01 &&
//            $this->number_of_samples > 1;
// }

/**
 * Get total transferred weight from this sample
 */
public function getTotalTransferredWeightAttribute(): float
{
    return $this->transfersFrom()->sum('transferred_weight');
}

/**
 * Get total transferred quantity from this sample
 */
public function getTotalTransferredQuantityAttribute(): int
{
    return $this->transfersFrom()->sum('transferred_quantity');
}




/**
 * Get all allocations for this sample
 */
public function allocations()
{
    return $this->hasMany(SampleAllocation::class);
}

/**
 * Get active allocations for this sample
 */
public function activeAllocations()
{
    return $this->hasMany(SampleAllocation::class)->active();
}

/**
 * Check if sample has sufficient weight for allocation
 */
public function hasSufficientWeightForAllocation(): bool
{
    return $this->available_weight >= self::FIXED_ALLOCATION_WEIGHT;
}

/**
 * Allocate 10gm for batch testing
 */
public function allocateForBatchTesting($batchGroupId, $batchId, $userId): SampleAllocation
{
    if (!$this->hasSufficientWeightForAllocation()) {
        throw new \Exception('Insufficient weight available for allocation. Available: ' . $this->available_weight . 'kg');
    }

    DB::beginTransaction();
    try {
        // Create allocation record
        $allocation = SampleAllocation::create([
            'sample_id' => $this->id,
            'batch_group_id' => $batchGroupId,
            'batch_id' => $batchId,
            'allocated_weight' => self::FIXED_ALLOCATION_WEIGHT,
            'allocation_type' => SampleAllocation::TYPE_BATCH_TESTING,
            'allocation_reason' => 'Allocated for batch testing',
            'allocation_date' => now(),
            'allocated_by' => $userId,
            'status' => SampleAllocation::STATUS_ALLOCATED
        ]);

        // Update sample allocation tracking
        $this->increment('allocation_count');
        $this->increment('allocated_weight', self::FIXED_ALLOCATION_WEIGHT);
        $this->decrement('available_weight', self::FIXED_ALLOCATION_WEIGHT);
        $this->update([
            'has_sufficient_weight' => $this->available_weight >= self::FIXED_ALLOCATION_WEIGHT
        ]);

        DB::commit();
        return $allocation;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

/**
 * Allocate 10gm for retesting
 */
public function allocateForRetesting($transferReason, $userId): SampleAllocation
{
    if (!$this->hasSufficientWeightForAllocation()) {
        throw new \Exception('Insufficient weight available for retesting allocation. Available: ' . $this->available_weight . 'kg');
    }

    DB::beginTransaction();
    try {
        // Create allocation record
        $allocation = SampleAllocation::create([
            'sample_id' => $this->id,
            'batch_group_id' => null, // Will be set when new sample is batched
            'batch_id' => null, // Will be set when new sample is batched
            'allocated_weight' => self::FIXED_ALLOCATION_WEIGHT,
            'allocation_type' => SampleAllocation::TYPE_RETESTING,
            'allocation_reason' => 'Allocated for ' . $transferReason,
            'allocation_date' => now(),
            'allocated_by' => $userId,
            'status' => SampleAllocation::STATUS_ALLOCATED
        ]);

        // Update sample allocation tracking
        $this->increment('allocation_count');
        $this->increment('allocated_weight', self::FIXED_ALLOCATION_WEIGHT);
        $this->decrement('available_weight', self::FIXED_ALLOCATION_WEIGHT);
        $this->update([
            'has_sufficient_weight' => $this->available_weight >= self::FIXED_ALLOCATION_WEIGHT
        ]);

        DB::commit();
        return $allocation;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

/**
 * Return allocated weight (if unused)
 */
public function returnAllocation(SampleAllocation $allocation): bool
{
    if ($allocation->sample_id !== $this->id) {
        throw new \Exception('Allocation does not belong to this sample');
    }

    if ($allocation->status !== SampleAllocation::STATUS_ALLOCATED) {
        throw new \Exception('Can only return allocated weight that has not been used');
    }

    DB::beginTransaction();
    try {
        // Update allocation status
        $allocation->update([
            'status' => SampleAllocation::STATUS_RETURNED,
            'remarks' => ($allocation->remarks ? $allocation->remarks . '. ' : '') . 'Returned on ' . now()->format('Y-m-d H:i')
        ]);

        // Update sample allocation tracking
        $this->decrement('allocation_count');
        $this->decrement('allocated_weight', $allocation->allocated_weight);
        $this->increment('available_weight', $allocation->allocated_weight);
        $this->update([
            'has_sufficient_weight' => $this->available_weight >= self::FIXED_ALLOCATION_WEIGHT
        ]);

        DB::commit();
        return true;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

/**
 * Mark allocation as used
 */
public function markAllocationAsUsed(SampleAllocation $allocation): bool
{
    if ($allocation->sample_id !== $this->id) {
        throw new \Exception('Allocation does not belong to this sample');
    }

    $allocation->update([
        'status' => SampleAllocation::STATUS_USED,
        'remarks' => ($allocation->remarks ? $allocation->remarks . '. ' : '') . 'Used on ' . now()->format('Y-m-d H:i')
    ]);

    return true;
}

/**
 * Check if sample can be transferred (has sufficient weight)
 */
public function canBeTransferred(): bool
{
    return $this->batch_group_id && 
           $this->evaluation_status === self::EVALUATION_COMPLETED &&
           $this->hasSufficientWeightForAllocation();
}

/**
 * Get total weight in catalog
 */
public function getCatalogWeightAttribute(): float
{
    return $this->attributes['catalog_weight'] ?? $this->sample_weight;
}

/**
 * Get allocation status summary
 */
public function getAllocationStatusAttribute(): string
{
    if (!$this->has_sufficient_weight) {
        return 'Insufficient Weight';
    }
    
    if ($this->allocation_count === 0) {
        return 'Available for Allocation';
    }
    
    return $this->allocation_count . ' allocation(s) made';
}
}