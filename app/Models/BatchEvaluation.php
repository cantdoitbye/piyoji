<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_group_id',
        'batch_id',
        'evaluation_date',
        'total_samples',
        'evaluation_status',
        'overall_remarks',
        'evaluation_started_by',
        'evaluation_completed_by',
        'evaluation_started_at',
        'evaluation_completed_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'evaluation_started_at' => 'datetime',
        'evaluation_completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the batch this evaluation belongs to
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'batch_group_id');
    }

    /**
     * Get all tester evaluations for this batch
     */
    public function testerEvaluations(): HasMany
    {
        return $this->hasMany(BatchTesterEvaluation::class);
    }

    /**
     * Get the user who started the evaluation
     */
    public function evaluationStartedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluation_started_by');
    }

    /**
     * Get the user who completed the evaluation
     */
    public function evaluationCompletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluation_completed_by');
    }

    /**
     * Get formatted status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->evaluation_status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
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
        return match($this->evaluation_status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_IN_PROGRESS => 'bg-info',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
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
     * Check if evaluation is in progress
     */
    public function isInProgress(): bool
    {
        return $this->evaluation_status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Get average scores from all testers
     */
    public function getAverageScoresAttribute(): array
    {
        $evaluations = $this->testerEvaluations;
        
        if ($evaluations->isEmpty()) {
            return [
                'c_score' => 0,
                't_score' => 0,
                's_score' => 0,
                'b_score' => 0,
                'total_samples' => 0
            ];
        }

        return [
            'c_score' => $evaluations->avg('c_score'),
            't_score' => $evaluations->avg('t_score'),
            's_score' => $evaluations->avg('s_score'),
            'b_score' => $evaluations->avg('b_score'),
            'total_samples' => $evaluations->sum('total_samples')
        ];
    }

    /**
     * Calculate overall batch acceptance status
     */
    public function getBatchAcceptanceAttribute(): string
    {
        $averageScores = $this->average_scores;
        $totalScore = $averageScores['c_score'] + $averageScores['t_score'] + 
                     $averageScores['s_score'] + $averageScores['b_score'];
        
        if ($totalScore >= 300) {
            return 'Accepted';
        } elseif ($totalScore >= 200) {
            return 'Normal';
        } else {
            return 'Rejected';
        }
    }
}