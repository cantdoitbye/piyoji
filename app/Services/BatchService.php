<?php

namespace App\Services;

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
}