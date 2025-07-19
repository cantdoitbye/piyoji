<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sample extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sample_id',
        'sample_name',
        'seller_id',
        'batch_id',
        'sample_weight',
        'arrival_date',
        'received_by',
        'status',
        'remarks',
        // Evaluation fields
        'aroma_score',
        'liquor_score',
        'appearance_score',
        'overall_score',
        'evaluation_comments',
        'evaluation_status',
        'evaluated_by',
        'evaluated_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'evaluated_at' => 'datetime',
        'sample_weight' => 'decimal:2',
        'aroma_score' => 'decimal:1',
        'liquor_score' => 'decimal:1',
        'appearance_score' => 'decimal:1',
        'overall_score' => 'decimal:1'
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

    /**
     * Get the seller that owns the sample.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
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
}