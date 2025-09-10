<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleTestingResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'testing_session_id',
        'sample_id',
        'sample_sequence',
        'tester_results',
        'status',
        'testing_completed_at',
        'tested_by',
        'sample_remarks'
    ];

    protected $casts = [
        'tester_results' => 'array',
        'testing_completed_at' => 'datetime'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the testing session this result belongs to
     */
    public function testingSession(): BelongsTo
    {
        return $this->belongsTo(BatchTestingSession::class, 'testing_session_id');
    }

    /**
     * Get the sample being tested
     */
    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }

    /**
     * Get the user who conducted the test
     */
    public function testedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    /**
     * Get formatted status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Mark this sample test as completed
     */
    public function markCompleted($userId, array $testerResults, string $remarks = null): void
    {
        $this->update([
            'tester_results' => $testerResults,
            'status' => self::STATUS_COMPLETED,
            'testing_completed_at' => now(),
            'tested_by' => $userId,
            'sample_remarks' => $remarks
        ]);
    }

    /**
     * Get average scores for this sample across all testers
     */
    public function getAverageScoresAttribute(): array
    {
        if (empty($this->tester_results)) {
            return [
                'c_score' => 0,
                't_score' => 0,
                's_score' => 0,
                'b_score' => 0
            ];
        }

        $totalTesters = count($this->tester_results);
        $totals = ['c_score' => 0, 't_score' => 0, 's_score' => 0, 'b_score' => 0];

        foreach ($this->tester_results as $testerResult) {
            $totals['c_score'] += $testerResult['c_score'] ?? 0;
            $totals['t_score'] += $testerResult['t_score'] ?? 0;
            $totals['s_score'] += $testerResult['s_score'] ?? 0;
            $totals['b_score'] += $testerResult['b_score'] ?? 0;
        }

        return [
            'c_score' => round($totals['c_score'] / $totalTesters, 1),
            't_score' => round($totals['t_score'] / $totalTesters, 1),
            's_score' => round($totals['s_score'] / $totalTesters, 1),
            'b_score' => round($totals['b_score'] / $totalTesters, 1)
        ];
    }

    /**
     * Get formatted average score result
     */
    public function getAverageScoreResultAttribute(): string
    {
        $averages = $this->average_scores;
        return "{$averages['c_score']}-{$averages['t_score']}-{$averages['s_score']}-{$averages['b_score']}";
    }

    /**
     * Get overall average score
     */
    public function getOverallAverageAttribute(): float
    {
        $averages = $this->average_scores;
        return round(($averages['c_score'] + $averages['t_score'] + $averages['s_score'] + $averages['b_score']) / 4, 1);
    }
}