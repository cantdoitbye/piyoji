<?php

namespace App\Services;

use App\Repositories\Interfaces\SampleRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Models\Sample;
use App\Models\SampleTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SampleService
{
    public function __construct(
        protected SampleRepositoryInterface $sampleRepository,
        protected SellerRepositoryInterface $sellerRepository
    ) {}

    /**
     * Get all samples with pagination and filters
     */
    public function getAllSamples(array $filters = [])
    {
        return $this->sampleRepository->getWithFilters($filters);
    }

    /**
     * Get sample by ID with detailed relationships
     */
    public function getSampleById($id)
    {
        return $this->sampleRepository->getSampleWithDetails($id);
    }

    /**
     * Create new sample (Module 2.1 - Sample Receiving)
     * Updated to support new fields: number_of_samples, weight_per_sample
     */
    public function createSample(array $data)
    {
        try {
            DB::beginTransaction();

            // Validate seller exists
            $seller = $this->sellerRepository->find($data['seller_id']);
            if (!$seller) {
                throw new \Exception('Seller not found');
            }

            // Generate unique sample ID
            $sampleId = Sample::generateSampleId();

            // Calculate total weight if weight per sample is provided
            $totalWeight = null;
            if (isset($data['weight_per_sample']) && isset($data['number_of_samples'])) {
                $totalWeight = $data['weight_per_sample'] * $data['number_of_samples'];
            }

            // Set default values
            $sampleData = array_merge($data, [
                'sample_id' => $sampleId,
                'status' => Sample::STATUS_RECEIVED,
                'evaluation_status' => Sample::EVALUATION_PENDING,
                'arrival_date' => $data['arrival_date'] ?? now()->format('Y-m-d'),
                'received_by' => $data['received_by'] ?? Auth::id(),
                'sample_weight' => $totalWeight,
                'number_of_samples' => $data['number_of_samples'] ?? 1,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            // Remove batch_id from data as it will be set during batching
            unset($sampleData['batch_id']);

            $sample = $this->sampleRepository->create($sampleData);

            DB::commit();

            return $sample;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update sample information
     */
    public function updateSample(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($id);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            // Recalculate total weight if weight per sample or number of samples changed
            if (isset($data['weight_per_sample']) || isset($data['number_of_samples'])) {
                $weightPerSample = $data['weight_per_sample'] ?? $sample->weight_per_sample;
                $numberOfSamples = $data['number_of_samples'] ?? $sample->number_of_samples;
                
                if ($weightPerSample && $numberOfSamples) {
                    $data['sample_weight'] = $weightPerSample * $numberOfSamples;
                }
            }

            $data['updated_by'] = Auth::id();
            $updatedSample = $this->sampleRepository->update($id, $data);

            DB::commit();

            return $updatedSample;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete sample
     */
    public function deleteSample(int $id)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($id);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            // Check if sample can be deleted (business rules)
            if ($sample->status === Sample::STATUS_ASSIGNED_TO_BUYERS) {
                throw new \Exception('Cannot delete sample that has been assigned to buyers');
            }

            // Check if sample is batched
            if ($sample->batch_group_id) {
                throw new \Exception('Cannot delete sample that is already batched. Please unbatch first.');
            }

            $this->sampleRepository->delete($id);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk create samples from Excel import
     * Updated to support new fields
     */
    public function bulkCreateSamples(array $samplesData, int $userId)
    {
        try {
            DB::beginTransaction();

            $created = 0;
            $errors = [];

            foreach ($samplesData as $index => $data) {
                try {
                    // Validate required fields
                    if (empty($data['sample_name']) || empty($data['seller_id'])) {
                        $errors[] = "Row " . ($index + 1) . ": Missing required fields (sample_name, seller_id)";
                        continue;
                    }

                    // Check if seller exists
                    $seller = $this->sellerRepository->find($data['seller_id']);
                    if (!$seller) {
                        $errors[] = "Row " . ($index + 1) . ": Seller not found";
                        continue;
                    }

                    // Generate unique sample ID
                    $sampleId = Sample::generateSampleId();

                    // Calculate total weight
                    $totalWeight = null;
                    if (isset($data['weight_per_sample']) && isset($data['number_of_samples'])) {
                        $totalWeight = $data['weight_per_sample'] * $data['number_of_samples'];
                    }

                    // Create sample
                    $sampleData = array_merge($data, [
                        'sample_id' => $sampleId,
                        'status' => Sample::STATUS_RECEIVED,
                        'evaluation_status' => Sample::EVALUATION_PENDING,
                        'arrival_date' => $data['arrival_date'] ?? now()->format('Y-m-d'),
                        'received_by' => $userId,
                        'sample_weight' => $totalWeight,
                        'number_of_samples' => $data['number_of_samples'] ?? 1,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Remove batch_id as it will be set during batching
                    unset($sampleData['batch_id']);

                    $this->sampleRepository->create($sampleData);
                    $created++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'created' => $created,
                'errors' => $errors,
                'total_processed' => count($samplesData)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get sample statistics including batch information
     */
    public function getSampleStatistics(): array
    {
        $today = Carbon::today();
        
        return [
            'total' => Sample::count(),
            'pending_evaluation' => Sample::where('evaluation_status', Sample::EVALUATION_PENDING)->count(),
            'evaluated' => Sample::where('evaluation_status', Sample::EVALUATION_COMPLETED)->count(),
            'approved' => Sample::where('status', Sample::STATUS_APPROVED)->count(),
            'rejected' => Sample::where('status', Sample::STATUS_REJECTED)->count(),
            'assigned_to_buyers' => Sample::where('status', Sample::STATUS_ASSIGNED_TO_BUYERS)->count(),
            'unbatched' => Sample::whereNull('batch_group_id')->count(),
            'batched' => Sample::whereNotNull('batch_group_id')->count(),
            'today' => Sample::whereDate('arrival_date', $today)->count(),
            'today_unbatched' => Sample::whereDate('arrival_date', $today)
                ->whereNull('batch_group_id')->count()
        ];
    }

    /**
     * Get samples for mobile app with batch information
     */
    public function getSamplesForMobile(int $userId, array $filters = [])
    {
        $query = Sample::with(['seller', 'batchGroup', 'receivedBy', 'evaluatedBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['evaluation_status'])) {
            $query->where('evaluation_status', $filters['evaluation_status']);
        }

        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (!empty($filters['batch_status'])) {
            if ($filters['batch_status'] === 'unbatched') {
                $query->whereNull('batch_group_id');
            } elseif ($filters['batch_status'] === 'batched') {
                $query->whereNotNull('batch_group_id');
            }
        }

        if (!empty($filters['user_samples_only']) && $filters['user_samples_only']) {
            $query->where('received_by', $userId);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get unbatched samples for a specific date
     */
    public function getUnbatchedSamplesForDate(Carbon $date): \Illuminate\Database\Eloquent\Collection
    {
        return Sample::with(['seller', 'receivedBy'])
            ->whereNull('batch_group_id')
            ->whereDate('arrival_date', $date)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get samples count by date for batching
     */
    public function getSamplesCountByDate(Carbon $date): int
    {
        return Sample::whereDate('arrival_date', $date)->count();
    }

    /**
     * Get unbatched samples count by date
     */
    public function getUnbatchedSamplesCountByDate(Carbon $date): int
    {
        return Sample::whereNull('batch_group_id')
            ->whereDate('arrival_date', $date)
            ->count();
    }

    // Sample Evaluation (Module 2.2) - Existing methods remain the same
    public function getPendingEvaluationSamples()
    {
        return Sample::with(['seller', 'receivedBy', 'batchGroup'])
            ->where('evaluation_status', Sample::EVALUATION_PENDING)
            ->orWhere('status', Sample::STATUS_PENDING_EVALUATION)
            ->whereNull('deleted_at')
            ->orderBy('arrival_date', 'asc')
            ->get();
    }

    public function getEvaluatedSamples()
    {
        return Sample::with(['seller', 'receivedBy', 'evaluatedBy', 'batchGroup'])
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->whereNull('deleted_at')
            ->orderBy('evaluated_at', 'desc')
            ->get();
    }

    public function getApprovedSamples()
    {
        return Sample::with(['seller', 'receivedBy', 'evaluatedBy', 'batchGroup'])
            ->where('status', Sample::STATUS_APPROVED)
            ->whereNull('deleted_at')
            ->orderBy('overall_score', 'desc')
            ->get();
    }

    public function getRejectedSamples()
    {
        return Sample::with(['seller', 'receivedBy', 'evaluatedBy', 'batchGroup'])
            ->where('status', Sample::STATUS_REJECTED)
            ->whereNull('deleted_at')
            ->orderBy('evaluated_at', 'desc')
            ->get();
    }

    /**
     * Start evaluation process
     */
    public function startEvaluation(int $sampleId, int $userId)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($sampleId);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            if ($sample->evaluation_status !== Sample::EVALUATION_PENDING) {
                throw new \Exception('Sample is not pending evaluation');
            }

            $sample->update([
                'evaluation_status' => Sample::EVALUATION_IN_PROGRESS,
                'evaluated_by' => $userId,
                'updated_by' => $userId
            ]);

            DB::commit();
            return $sample;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Store evaluation results
     */
    public function storeEvaluation(int $sampleId, array $evaluationData, int $userId)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($sampleId);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            // Calculate overall score if individual scores are provided
            if (isset($evaluationData['color_score']) && 
                isset($evaluationData['taste_score']) && 
                isset($evaluationData['strength_score']) && 
                isset($evaluationData['briskness_score'])) {
                
                $overallScore = ($evaluationData['color_score'] + 
                               $evaluationData['taste_score'] + 
                               $evaluationData['strength_score'] + 
                               $evaluationData['briskness_score']) / 4;
                $evaluationData['overall_score'] = round($overallScore, 1);
            }

            // Determine status based on score
            $status = Sample::STATUS_EVALUATED;
            if (isset($evaluationData['overall_score'])) {
                $status = $evaluationData['overall_score'] >= 6 ? 
                         Sample::STATUS_APPROVED : 
                         Sample::STATUS_REJECTED;
            }

            $updateData = array_merge($evaluationData, [
                'evaluation_status' => Sample::EVALUATION_COMPLETED,
                'status' => $status,
                'evaluated_by' => $userId,
                'evaluated_at' => now(),
                'updated_by' => $userId
            ]);
            $sample->update($updateData);

            DB::commit();
            return $sample;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate tasting report
     */
    public function generateTastingReport(array $filters = [])
    {
        $query = Sample::with(['seller', 'evaluatedBy', 'batchGroup'])
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->whereNull('deleted_at');

        // Apply filters
        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (!empty($filters['min_score'])) {
            $query->where('overall_score', '>=', $filters['min_score']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('evaluated_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('evaluated_at', '<=', $filters['end_date']);
        }

        $samples = $query->orderBy('overall_score', 'desc')->get();

        // Calculate statistics
        $statistics = [
            'total_evaluated' => $samples->count(),
            'average_score' => $samples->avg('overall_score'),
            'highest_score' => $samples->max('overall_score'),
            'lowest_score' => $samples->min('overall_score'),
            'approved_count' => $samples->where('status', Sample::STATUS_APPROVED)->count(),
            'rejected_count' => $samples->where('status', Sample::STATUS_REJECTED)->count()
        ];

        return [
            'samples' => $samples,
            'statistics' => $statistics,
            'filters' => $filters
        ];
    }

    /**
     * Transfer sample to another batch for retesting
     */
    public function transferSampleToBatch(int $sampleId, array $transferData): array
    {
        try {
            DB::beginTransaction();

            $originalSample = Sample::with(['batchGroup', 'seller'])->findOrFail($sampleId);

            // Validate transfer data
            $this->validateTransferData($originalSample, $transferData);

            // Calculate remaining weights and quantities
            $remainingWeight = $originalSample->sample_weight - $transferData['transferred_weight'];
            $remainingQuantity = $originalSample->number_of_samples - $transferData['transferred_quantity'];

            // Create new sample for the transferred portion
            $newSample = $this->createTransferredSample($originalSample, $transferData);

            // Update original sample with remaining portion
            $this->updateOriginalSample($originalSample, $remainingWeight, $remainingQuantity);

            // Create transfer record
            $transfer = SampleTransfer::create([
                'original_sample_id' => $originalSample->id,
                'new_sample_id' => $newSample->id,
                'from_batch_group_id' => $originalSample->batch_group_id,
                'to_batch_group_id' => null, // Will be set when new sample is batched
                'from_batch_id' => $originalSample->batch_id,
                'to_batch_id' => null, // Will be set when new sample is batched
                'transferred_weight' => $transferData['transferred_weight'],
                'transferred_quantity' => $transferData['transferred_quantity'],
                'remaining_weight' => $remainingWeight,
                'remaining_quantity' => $remainingQuantity,
                'transfer_reason' => $transferData['transfer_reason'],
                'transfer_remarks' => $transferData['transfer_remarks'] ?? null,
                'status' => SampleTransfer::STATUS_COMPLETED,
                'transfer_date' => now(),
                'transferred_by' => Auth::id()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Sample successfully transferred for retesting',
                'original_sample' => $originalSample->fresh(),
                'new_sample' => $newSample,
                'transfer' => $transfer
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate transfer data
     */
    private function validateTransferData(Sample $sample, array $data): void
    {
        // Check if sample is batched
        if (!$sample->batch_group_id) {
            throw new \Exception('Sample must be batched before it can be transferred');
        }

        // Check if sample has been evaluated
        if ($sample->evaluation_status !== Sample::EVALUATION_COMPLETED) {
            throw new \Exception('Only evaluated samples can be transferred for retesting');
        }

        // Validate transferred weight
        if ($data['transferred_weight'] <= 0 || $data['transferred_weight'] > $sample->sample_weight) {
            throw new \Exception('Transferred weight must be greater than 0 and not exceed available weight');
        }

        // Validate transferred quantity
        if ($data['transferred_quantity'] <= 0 || $data['transferred_quantity'] > $sample->number_of_samples) {
            throw new \Exception('Transferred quantity must be greater than 0 and not exceed available quantity');
        }

        // Ensure at least some portion remains in original sample
        if ($data['transferred_weight'] == $sample->sample_weight && $data['transferred_quantity'] == $sample->number_of_samples) {
            throw new \Exception('Cannot transfer entire sample. Some portion must remain in the original sample');
        }
    }

    /**
     * Create new sample for transferred portion
     */
    private function createTransferredSample(Sample $originalSample, array $transferData): Sample
    {
        // Generate new sample ID
        $newSampleId = Sample::generateSampleId();

        // Calculate weight per sample for new sample
        $weightPerSample = $transferData['transferred_quantity'] > 0 
            ? $transferData['transferred_weight'] / $transferData['transferred_quantity'] 
            : $transferData['transferred_weight'];

        $newSample = Sample::create([
            'sample_id' => $newSampleId,
            'sample_name' => $originalSample->sample_name . ' (Retesting)',
            'seller_id' => $originalSample->seller_id,
            'batch_id' => null, // Will be assigned when batched
            'batch_group_id' => null, // Will be assigned when batched
            'number_of_samples' => $transferData['transferred_quantity'],
            'weight_per_sample' => $weightPerSample,
            'sample_weight' => $transferData['transferred_weight'],
            'arrival_date' => now()->format('Y-m-d'),
            'received_by' => Auth::id(),
            'status' => Sample::STATUS_RECEIVED,
            'evaluation_status' => Sample::EVALUATION_PENDING,
            'remarks' => 'Transferred from ' . $originalSample->sample_id . ' for ' . $transferData['transfer_reason'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return $newSample;
    }

    /**
     * Update original sample with remaining portion
     */
    private function updateOriginalSample(Sample $sample, float $remainingWeight, int $remainingQuantity): void
    {
        $weightPerSample = $remainingQuantity > 0 ? $remainingWeight / $remainingQuantity : 0;

        $sample->update([
            'number_of_samples' => $remainingQuantity,
            'weight_per_sample' => $weightPerSample,
            'sample_weight' => $remainingWeight,
            'updated_by' => Auth::id(),
            'remarks' => ($sample->remarks ? $sample->remarks . '. ' : '') . 'Partial transfer completed on ' . now()->format('Y-m-d')
        ]);
    }

    /**
     * Get transfer history for a sample
     */
    public function getSampleTransferHistory(int $sampleId): array
    {
        $sample = Sample::findOrFail($sampleId);

        $transfersFrom = SampleTransfer::with(['newSample', 'toBatchGroup', 'transferredBy'])
            ->where('original_sample_id', $sampleId)
            ->orderBy('transfer_date', 'desc')
            ->get();

        $transfersTo = SampleTransfer::with(['originalSample', 'fromBatchGroup', 'transferredBy'])
            ->where('new_sample_id', $sampleId)
            ->orderBy('transfer_date', 'desc')
            ->get();

        return [
            'sample' => $sample,
            'transfers_from' => $transfersFrom,
            'transfers_to' => $transfersTo,
            'total_transfers' => $transfersFrom->count() + $transfersTo->count()
        ];
    }

    /**
     * Get all sample transfers with filters
     */
    public function getAllTransfers(array $filters = [])
    {
        $query = SampleTransfer::with([
            'originalSample.seller',
            'newSample.seller', 
            'fromBatchGroup',
            'toBatchGroup',
            'transferredBy'
        ])->orderBy('transfer_date', 'desc');

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['transfer_reason'])) {
            $query->where('transfer_reason', $filters['transfer_reason']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('transfer_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('transfer_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('originalSample', function ($q) use ($search) {
                $q->where('sample_id', 'like', '%' . $search . '%')
                  ->orWhere('sample_name', 'like', '%' . $search . '%');
            })->orWhereHas('newSample', function ($q) use ($search) {
                $q->where('sample_id', 'like', '%' . $search . '%')
                  ->orWhere('sample_name', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Cancel a pending transfer
     */
    public function cancelTransfer(int $transferId): bool
    {
        try {
            DB::beginTransaction();

            $transfer = SampleTransfer::findOrFail($transferId);

            if ($transfer->status !== SampleTransfer::STATUS_PENDING) {
                throw new \Exception('Only pending transfers can be cancelled');
            }

            $transfer->update([
                'status' => SampleTransfer::STATUS_CANCELLED,
                'updated_at' => now()
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}