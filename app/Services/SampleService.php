<?php

namespace App\Services;

use App\Repositories\Interfaces\SampleRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Models\Sample;
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
            if (isset($evaluationData['aroma_score']) && 
                isset($evaluationData['liquor_score']) && 
                isset($evaluationData['appearance_score'])) {
                
                $overallScore = ($evaluationData['aroma_score'] + 
                               $evaluationData['liquor_score'] + 
                               $evaluationData['appearance_score']) / 3;
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
}