<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchTesterEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_evaluation_id',
        'tester_poc_id',
        'tester_name',
        'c_score',
        't_score',
        's_score', 
        'b_score',
        'total_samples',
        'color_shade',
        'brand',
        'remarks',
        'evaluation_status',
        'evaluated_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'c_score' => 'integer',
        't_score' => 'integer',
        's_score' => 'integer',
        'b_score' => 'integer',
        'total_samples' => 'integer',
        'evaluated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the batch evaluation this belongs to
     */
    public function batchEvaluation(): BelongsTo
    {
        return $this->belongsTo(BatchEvaluation::class);
    }

    /**
     * Get the tester POC
     */
    public function testerPoc(): BelongsTo
    {
        return $this->belongsTo(Poc::class, 'tester_poc_id');
    }

    /**
     * Get the user who created this evaluation
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get score result in C-T-S-B format
     */
    public function getScoreResultAttribute(): string
    {
        return "{$this->c_score}-{$this->t_score}-{$this->s_score}-{$this->b_score}";
    }

    /**
     * Calculate total score
     */
    public function getTotalScoreAttribute(): int
    {
        return $this->c_score + $this->t_score + $this->s_score + $this->b_score;
    }

    /**
     * Get evaluation result based on total score
     */
    public function getEvaluationResultAttribute(): string
    {
        $totalScore = $this->total_score;
        
        if ($totalScore >= 300) {
            return 'Accepted';
        } elseif ($totalScore >= 200) {
            return 'Normal';
        } else {
            return 'Rejected';
        }
    }

    /**
     * Get evaluation result badge class
     */
    public function getResultBadgeClassAttribute(): string
    {
        return match($this->evaluation_result) {
            'Accepted' => 'bg-success',
            'Normal' => 'bg-warning',
            'Rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    /**
     * Check if evaluation is completed
     */
    public function isCompleted(): bool
    {
        return $this->evaluation_status === self::STATUS_COMPLETED;
    }

    /**
     * Get formatted status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->evaluation_status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown'
        };
    }
}