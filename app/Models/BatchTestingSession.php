<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchTestingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_group_id',
        'batch_id',
        'testers',
        'total_samples',
        'current_sample_index',
        'status',
        'session_started_at',
        'session_completed_at',
        'initiated_by',
        'remarks'
    ];

    protected $casts = [
        'testers' => 'array',
        'session_started_at' => 'datetime',
        'session_completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_INITIATED = 'initiated';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the batch this session belongs to
     */
    public function batchGroup(): BelongsTo
    {
        return $this->belongsTo(SampleBatch::class, 'batch_group_id');
    }

    /**
     * Get the user who initiated this session
     */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get all sample testing results for this session
     */
    public function sampleResults(): HasMany
    {
        return $this->hasMany(SampleTestingResult::class, 'testing_session_id');
    }

    /**
     * Get completed sample results
     */
    public function completedResults(): HasMany
    {
        return $this->hasMany(SampleTestingResult::class, 'testing_session_id')
                    ->where('status', SampleTestingResult::STATUS_COMPLETED);
    }

    /**
     * Get pending sample results
     */
    public function pendingResults(): HasMany
    {
        return $this->hasMany(SampleTestingResult::class, 'testing_session_id')
                    ->where('status', SampleTestingResult::STATUS_PENDING);
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_INITIATED => 'Initiated',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Get current sample for testing
     */
    public function getCurrentSample()
    {
        if ($this->current_sample_index >= $this->total_samples) {
            return null;
        }

        return $this->sampleResults()
                    ->where('sample_sequence', $this->current_sample_index + 1)
                    ->with('sample')
                    ->first();
    }

    /**
     * Get next sample for testing
     */
    public function getNextSample()
    {
        $nextIndex = $this->current_sample_index + 1;
        
        if ($nextIndex >= $this->total_samples) {
            return null;
        }

        return $this->sampleResults()
                    ->where('sample_sequence', $nextIndex + 1)
                    ->with('sample')
                    ->first();
    }

    /**
     * Mark current sample as completed and move to next
     */
    public function moveToNextSample(): bool
    {
        if ($this->current_sample_index < $this->total_samples - 1) {
            $this->increment('current_sample_index');
            
            // Start session if it's the first sample
            if ($this->status === self::STATUS_INITIATED) {
                $this->update([
                    'status' => self::STATUS_IN_PROGRESS,
                    'session_started_at' => now()
                ]);
            }
            
            return true;
        }
        
        // If this was the last sample, mark session as completed
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'session_completed_at' => now()
        ]);
        
        return false;
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute(): float
    {
        if ($this->total_samples == 0) {
            return 0;
        }
        
        $completedCount = $this->completedResults()->count();
        return round(($completedCount / $this->total_samples) * 100, 1);
    }

    /**
     * Check if session can be started
     */
    public function canStart(): bool
    {
        return $this->status === self::STATUS_INITIATED;
    }

    /**
     * Check if session is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}