<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SampleBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'batch_date',
        'batch_sequence',
        'total_samples',
        'max_samples',
        'status',
        'remarks',
        'completed_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'batch_date' => 'date',
        'completed_at' => 'datetime',
        'total_samples' => 'integer',
        'max_samples' => 'integer'
    ];

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_FULL = 'full';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the samples that belong to this batch
     */
    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class, 'batch_group_id');
    }

    /**
     * Get the user who created the batch
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the batch
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Generate unique batch number
     */
    public static function generateBatchNumber(Carbon $date, int $sequence): string
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        
        return 'BATCH' . $year . $month . $day . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get next sequence number for a specific date
     */
    public static function getNextSequence(Carbon $date): int
    {
        $lastBatch = self::where('batch_date', $date->format('Y-m-d'))
            ->orderBy('batch_sequence', 'desc')
            ->first();
        
        return $lastBatch ? $lastBatch->batch_sequence + 1 : 1;
    }

    /**
     * Check if batch can accept more samples
     */
    public function canAcceptSamples(int $sampleCount = 1): bool
    {
        return $this->status === self::STATUS_OPEN && 
               ($this->total_samples + $sampleCount) <= $this->max_samples;
    }

    /**
     * Add samples to batch
     */
    public function addSamples(int $count): void
    {
        $this->total_samples += $count;
        
        if ($this->total_samples >= $this->max_samples) {
            $this->status = self::STATUS_FULL;
        }
        
        $this->save();
    }

    /**
     * Mark batch as completed
     */
    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_FULL => 'Full',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_OPEN => 'bg-success',
            self::STATUS_FULL => 'bg-warning',
            self::STATUS_PROCESSING => 'bg-info',
            self::STATUS_COMPLETED => 'bg-secondary',
            default => 'bg-light'
        };
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacityAttribute(): int
    {
        return $this->max_samples - $this->total_samples;
    }

    /**
     * Get capacity percentage
     */
    public function getCapacityPercentageAttribute(): float
    {
        return ($this->total_samples / $this->max_samples) * 100;
    }

    // Scopes
    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('batch_date', $date->format('Y-m-d'));
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeFull($query)
    {
        return $query->where('status', self::STATUS_FULL);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}