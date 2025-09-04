<?php

namespace App\Services;

use App\Models\BatchEvaluation;
use App\Models\BatchTesterEvaluation;
use App\Models\Poc;
use App\Models\Sample;
use App\Models\SampleBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BatchService
{
    /**
     * Create batches for samples on a specific date
     * Each batch will contain exactly 48 samples
     */
    public function createBatchesForDate(Carbon $date, int $userId): array
    {
        try {
            DB::beginTransaction();

            // Get all unbatched samples for the specified date
            $unbatchedSamples = Sample::unbatched()
                ->forDate($date)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($unbatchedSamples->isEmpty()) {
                throw new \Exception('No unbatched samples found for ' . $date->format('Y-m-d'));
            }

            $batchesCreated = [];
            $samplesProcessed = 0;
            $totalSamples = $unbatchedSamples->count();

            // Process samples in batches of 48
            $sampleChunks = $unbatchedSamples->chunk(48);

            foreach ($sampleChunks as $chunk) {
                // Create new batch
                $sequence = SampleBatch::getNextSequence($date);
                $batchNumber = SampleBatch::generateBatchNumber($date, $sequence);

                $batch = SampleBatch::create([
                    'batch_number' => $batchNumber,
                    'batch_date' => $date->format('Y-m-d'),
                    'batch_sequence' => $sequence,
                    'total_samples' => $chunk->count(),
                    'max_samples' => 48,
                    'status' => $chunk->count() >= 48 ? SampleBatch::STATUS_FULL : SampleBatch::STATUS_OPEN,
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);

                // Assign samples to batch and generate batch IDs
                $batchSampleNumber = 1;
                foreach ($chunk as $sample) {
                    $sampleBatchId = $batchNumber . '-' . str_pad($batchSampleNumber, 2, '0', STR_PAD_LEFT);
                    
                    $sample->update([
                        'batch_group_id' => $batch->id,
                        'batch_id' => $sampleBatchId,
                        'updated_by' => $userId
                    ]);

                    $batchSampleNumber++;
                    $samplesProcessed++;
                }

                $batchesCreated[] = [
                    'batch' => $batch,
                    'samples_count' => $chunk->count()
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'batches_created' => count($batchesCreated),
                'samples_processed' => $samplesProcessed,
                'total_samples' => $totalSamples,
                'batches' => $batchesCreated,
                'date' => $date->format('Y-m-d')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get batch statistics for a specific date
     */
    public function getBatchStatisticsForDate(Carbon $date): array
    {
        $batches = SampleBatch::forDate($date)->with('samples')->get();
        $unbatchedSamples = Sample::unbatched()->forDate($date)->count();

        return [
            'date' => $date->format('Y-m-d'),
            'total_batches' => $batches->count(),
            'total_batched_samples' => $batches->sum('total_samples'),
            'unbatched_samples' => $unbatchedSamples,
            'batches' => $batches->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'status' => $batch->status,
                    'status_label' => $batch->status_label,
                    'total_samples' => $batch->total_samples,
                    'capacity_percentage' => $batch->capacity_percentage,
                    'created_at' => $batch->created_at->format('H:i:s')
                ];
            })
        ];
    }

    /**
     * Get all batches with pagination and filters
     */
    public function getAllBatches(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = SampleBatch::with(['samples', 'createdBy'])
            ->orderBy('batch_date', 'desc')
            ->orderBy('batch_sequence', 'desc');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('batch_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('batch_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('batch_number', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get batch details with samples
     */
    public function getBatchDetails(int $batchId): SampleBatch
    {
        return SampleBatch::with([
            'samples.seller',
            'samples.receivedBy',
            'samples.evaluatedBy',
            'createdBy',
            'updatedBy'
        ])->findOrFail($batchId);
    }

    /**
     * Get batch statistics overview
     */
    public function getBatchOverview(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'batches' => SampleBatch::forDate($today)->count(),
                'samples' => SampleBatch::forDate($today)->sum('total_samples'),
                'unbatched_samples' => Sample::unbatched()->forDate($today)->count()
            ],
            'this_week' => [
                'batches' => SampleBatch::where('batch_date', '>=', $thisWeek)->count(),
                'samples' => SampleBatch::where('batch_date', '>=', $thisWeek)->sum('total_samples')
            ],
            'this_month' => [
                'batches' => SampleBatch::where('batch_date', '>=', $thisMonth)->count(),
                'samples' => SampleBatch::where('batch_date', '>=', $thisMonth)->sum('total_samples')
            ],
            'total' => [
                'batches' => SampleBatch::count(),
                'samples' => SampleBatch::sum('total_samples')
            ]
        ];
    }

    /**
     * Delete a batch and unbatch its samples
     */
    public function deleteBatch(int $batchId, int $userId): bool
    {
        try {
            DB::beginTransaction();

            $batch = SampleBatch::findOrFail($batchId);

            // Unbatch all samples in this batch
            Sample::where('batch_group_id', $batchId)->update([
                'batch_group_id' => null,
                'batch_id' => null,
                'updated_by' => $userId
            ]);

            // Delete the batch
            $batch->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rebuild batches for a specific date
     * This will unbatch all samples for the date and create new batches
     */
    public function rebuildBatchesForDate(Carbon $date, int $userId): array
    {
        try {
            DB::beginTransaction();

            // First, unbatch all samples for this date
            $existingBatches = SampleBatch::forDate($date)->get();
            foreach ($existingBatches as $batch) {
                Sample::where('batch_group_id', $batch->id)->update([
                    'batch_group_id' => null,
                    'batch_id' => null,
                    'updated_by' => $userId
                ]);
                $batch->delete();
            }

            // Now create new batches
            $result = $this->createBatchesForDate($date, $userId);

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get batch evaluation statistics
     */
    public function getBatchEvaluationStatistics(): array
    {
        $totalBatches = SampleBatch::count();
        $evaluatedBatches = BatchEvaluation::where('evaluation_status', BatchEvaluation::STATUS_COMPLETED)->count();
        $pendingEvaluations = BatchEvaluation::where('evaluation_status', BatchEvaluation::STATUS_PENDING)->count();
        $inProgressEvaluations = BatchEvaluation::where('evaluation_status', BatchEvaluation::STATUS_IN_PROGRESS)->count();
        
        return [
            'total_batches' => $totalBatches,
            'evaluated_batches' => $evaluatedBatches,
            'pending_evaluations' => $pendingEvaluations,
            'in_progress_evaluations' => $inProgressEvaluations,
            'evaluation_completion_rate' => $totalBatches > 0 ? round(($evaluatedBatches / $totalBatches) * 100, 1) : 0
        ];
    }

    /**
     * Get batch evaluation details
     */
    public function getBatchEvaluationDetails(int $batchId): BatchEvaluation
    {
        return BatchEvaluation::with([
            'batch',
            'testerEvaluations.testerPoc',
            'evaluationStartedBy',
            'evaluationCompletedBy'
        ])->where('batch_group_id', $batchId)->firstOrFail();
    }

    /**
     * Create batch evaluation
     */
    public function createBatchEvaluation(int $batchId, array $evaluationData, int $userId): BatchEvaluation
    {
        try {
            DB::beginTransaction();

            $batch = SampleBatch::findOrFail($batchId);

            // Create batch evaluation
            $evaluation = BatchEvaluation::create([
                'batch_group_id' => $batchId,
                'batch_id' => $batch->batch_number,
                'evaluation_date' => now()->toDateString(),
                'total_samples' => $batch->total_samples,
                'evaluation_status' => BatchEvaluation::STATUS_IN_PROGRESS,
                'overall_remarks' => $evaluationData['overall_remarks'] ?? null,
                'evaluation_started_by' => $userId,
                'evaluation_started_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId
            ]);

            // Create tester evaluations
            foreach ($evaluationData['testers'] as $testerData) {
                $tester = Poc::findOrFail($testerData['tester_poc_id']);
                
                BatchTesterEvaluation::create([
                    'batch_evaluation_id' => $evaluation->id,
                    'tester_poc_id' => $tester->id,
                    'tester_name' => $tester->poc_name,
                    'c_score' => $testerData['c_score'],
                    't_score' => $testerData['t_score'],
                    's_score' => $testerData['s_score'],
                    'b_score' => $testerData['b_score'],
                    'total_samples' => $testerData['total_samples'],
                    'color_shade' => $testerData['color_shade'] ?? 'RED',
                    'brand' => $testerData['brand'] ?? 'WB',
                    'remarks' => $testerData['remarks'] ?? 'NORMAL',
                    'evaluation_status' => BatchTesterEvaluation::STATUS_COMPLETED,
                    'evaluated_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
            }

            // Complete the evaluation
            $evaluation->update([
                'evaluation_status' => BatchEvaluation::STATUS_COMPLETED,
                'evaluation_completed_by' => $userId,
                'evaluation_completed_at' => now(),
                'updated_by' => $userId
            ]);

            // Update batch status based on evaluation results
            $this->updateBatchStatusFromEvaluation($batch, $evaluation);

            DB::commit();
            return $evaluation;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update batch evaluation
     */
    public function updateBatchEvaluation(int $evaluationId, array $evaluationData, int $userId): BatchEvaluation
    {
        try {
            DB::beginTransaction();

            $evaluation = BatchEvaluation::findOrFail($evaluationId);
            
            // Update evaluation
            $evaluation->update([
                'overall_remarks' => $evaluationData['overall_remarks'] ?? null,
                'evaluation_completed_by' => $userId,
                'evaluation_completed_at' => now(),
                'updated_by' => $userId
            ]);

            // Delete existing tester evaluations
            BatchTesterEvaluation::where('batch_evaluation_id', $evaluationId)->delete();

            // Create new tester evaluations
            foreach ($evaluationData['testers'] as $testerData) {
                $tester = Poc::findOrFail($testerData['tester_poc_id']);
                
                BatchTesterEvaluation::create([
                    'batch_evaluation_id' => $evaluation->id,
                    'tester_poc_id' => $tester->id,
                    'tester_name' => $tester->poc_name,
                    'c_score' => $testerData['c_score'],
                    't_score' => $testerData['t_score'],
                    's_score' => $testerData['s_score'],
                    'b_score' => $testerData['b_score'],
                    'total_samples' => $testerData['total_samples'],
                    'color_shade' => $testerData['color_shade'] ?? 'RED',
                    'brand' => $testerData['brand'] ?? 'WB',
                    'remarks' => $testerData['remarks'] ?? 'NORMAL',
                    'evaluation_status' => BatchTesterEvaluation::STATUS_COMPLETED,
                    'evaluated_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId
                ]);
            }

            // Update batch status based on evaluation results
            $batch = $evaluation->batch;
            $this->updateBatchStatusFromEvaluation($batch, $evaluation);

            DB::commit();
            return $evaluation->fresh(['testerEvaluations.testerPoc']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get evaluation report data
     */
    public function getEvaluationReport(array $filters = []): array
    {
        $query = BatchEvaluation::with([
            'batch',
            'testerEvaluations.testerPoc',
            'evaluationStartedBy',
            'evaluationCompletedBy'
        ]);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('evaluation_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('evaluation_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('evaluation_status', $filters['status']);
        }

        if (!empty($filters['tester_id'])) {
            $query->whereHas('testerEvaluations', function ($q) use ($filters) {
                $q->where('tester_poc_id', $filters['tester_id']);
            });
        }

        $evaluations = $query->orderBy('evaluation_date', 'desc')->get();

        // Calculate statistics
        $statistics = [
            'total_evaluations' => $evaluations->count(),
            'completed_evaluations' => $evaluations->where('evaluation_status', BatchEvaluation::STATUS_COMPLETED)->count(),
            'average_total_score' => $evaluations->avg(function ($evaluation) {
                $scores = $evaluation->average_scores;
                return $scores['c_score'] + $scores['t_score'] + $scores['s_score'] + $scores['b_score'];
            }),
            'accepted_batches' => $evaluations->filter(function ($evaluation) {
                return $evaluation->batch_acceptance === 'Accepted';
            })->count(),
            'normal_batches' => $evaluations->filter(function ($evaluation) {
                return $evaluation->batch_acceptance === 'Normal';
            })->count(),
            'rejected_batches' => $evaluations->filter(function ($evaluation) {
                return $evaluation->batch_acceptance === 'Rejected';
            })->count()
        ];

        return [
            'evaluations' => $evaluations,
            'statistics' => $statistics,
            'filters' => $filters
        ];
    }

    /**
     * Update batch status based on evaluation results
     */
    private function updateBatchStatusFromEvaluation(SampleBatch $batch, BatchEvaluation $evaluation): void
    {
        $averageScores = $evaluation->average_scores;
        $totalScore = $averageScores['c_score'] + $averageScores['t_score'] + 
                     $averageScores['s_score'] + $averageScores['b_score'];
        
        // Update batch status based on evaluation result
        if ($totalScore >= 300) {
            $batch->update([
                'status' => 'completed',
                'completed_at' => now(),
                'updated_by' => auth()->id()
            ]);
        } elseif ($totalScore >= 200) {
            $batch->update([
                'status' => 'processing',
                'updated_by' => auth()->id()
            ]);
        } else {
            $batch->update([
                'status' => 'processing', // Keep as processing for rejected batches for potential re-evaluation
                'updated_by' => auth()->id()
            ]);
        }
    }
}