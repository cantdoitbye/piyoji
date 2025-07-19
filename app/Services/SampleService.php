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
    public function getSampleById(int $id)
    {
        return $this->sampleRepository->getSampleWithDetails($id);
    }

    /**
     * Create new sample (Module 2.1 - Sample Receiving)
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

            // Check for duplicate batch ID for the same seller
            if ($this->sampleRepository->checkBatchIdExists($data['batch_id'], $data['seller_id'])) {
                throw new \Exception('Batch ID already exists for this seller');
            }

            // Set default values
            $sampleData = array_merge($data, [
                'status' => Sample::STATUS_RECEIVED,
                'evaluation_status' => Sample::EVALUATION_PENDING,
                'arrival_date' => $data['arrival_date'] ?? now()->format('Y-m-d'),
                'received_by' => $data['received_by'] ?? Auth::id(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

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

            // Check for duplicate batch ID if batch_id is being updated
            if (isset($data['batch_id']) && $data['batch_id'] !== $sample->batch_id) {
                if ($this->sampleRepository->checkBatchIdExists($data['batch_id'], $sample->seller_id, $id)) {
                    throw new \Exception('Batch ID already exists for this seller');
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
                    if (empty($data['sample_name']) || empty($data['seller_id']) || empty($data['batch_id'])) {
                        $errors[] = "Row " . ($index + 1) . ": Missing required fields";
                        continue;
                    }

                    // Check if seller exists
                    $seller = $this->sellerRepository->find($data['seller_id']);
                    if (!$seller) {
                        $errors[] = "Row " . ($index + 1) . ": Seller not found";
                        continue;
                    }

                    // Check for duplicate batch ID
                    if ($this->sampleRepository->checkBatchIdExists($data['batch_id'], $data['seller_id'])) {
                        $errors[] = "Row " . ($index + 1) . ": Batch ID already exists for this seller";
                        continue;
                    }

                    // Create sample
                    $sampleData = array_merge($data, [
                        'status' => Sample::STATUS_RECEIVED,
                        'evaluation_status' => Sample::EVALUATION_PENDING,
                        'arrival_date' => $data['arrival_date'] ?? now()->format('Y-m-d'),
                        'received_by' => $userId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

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
                'total' => count($samplesData)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Start sample evaluation (Module 2.2)
     */
    public function startEvaluation(int $id, int $evaluatorId)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($id);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            if ($sample->evaluation_status !== Sample::EVALUATION_PENDING) {
                throw new \Exception('Sample evaluation already started or completed');
            }

            $this->sampleRepository->update($id, [
                'evaluation_status' => Sample::EVALUATION_IN_PROGRESS,
                'status' => Sample::STATUS_PENDING_EVALUATION,
                'evaluated_by' => $evaluatorId,
                'updated_by' => $evaluatorId
            ]);

            DB::commit();

            return $this->sampleRepository->getSampleWithDetails($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Save sample evaluation (Module 2.2)
     */
    public function saveEvaluation(int $id, array $evaluationData)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($id);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            // Validate evaluation scores
            $this->validateEvaluationScores($evaluationData);

            // Calculate overall score
            $overallScore = $this->calculateOverallScore(
                $evaluationData['aroma_score'],
                $evaluationData['liquor_score'],
                $evaluationData['appearance_score']
            );

            // Determine status based on overall score
            $status = $overallScore >= 7.0 ? Sample::STATUS_APPROVED : Sample::STATUS_REJECTED;

            $updateData = [
                'aroma_score' => $evaluationData['aroma_score'],
                'liquor_score' => $evaluationData['liquor_score'],
                'appearance_score' => $evaluationData['appearance_score'],
                'overall_score' => $overallScore,
                'evaluation_comments' => $evaluationData['evaluation_comments'] ?? null,
                'evaluation_status' => Sample::EVALUATION_COMPLETED,
                'status' => $status,
                'evaluated_by' => $evaluationData['evaluated_by'] ?? Auth::id(),
                'evaluated_at' => now(),
                'updated_by' => Auth::id()
            ];

            $this->sampleRepository->update($id, $updateData);

            DB::commit();

            return $this->sampleRepository->getSampleWithDetails($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get samples pending evaluation
     */
    public function getPendingEvaluationSamples()
    {
        return $this->sampleRepository->getPendingEvaluationSamples();
    }

    /**
     * Get evaluated samples
     */
    public function getEvaluatedSamples()
    {
        return $this->sampleRepository->getEvaluatedSamples();
    }

    /**
     * Get approved samples
     */
    public function getApprovedSamples()
    {
        return $this->sampleRepository->getApprovedSamples();
    }

    /**
     * Get top scoring samples for buyer assignment
     */
    public function getTopScoringSamples(float $minScore = 8.0)
    {
        return $this->sampleRepository->getTopScoringSamples($minScore);
    }

    /**
     * Generate tasting report
     */
    public function generateTastingReport(array $filters = [])
    {
        $samples = $this->sampleRepository->getSamplesForTastingReport($filters);
        
        return [
            'samples' => $samples,
            'total_count' => $samples->count(),
            'average_score' => $samples->avg('overall_score'),
            'filters_applied' => $filters,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name ?? 'System'
        ];
    }

    /**
     * Get sample statistics
     */
    public function getSampleStatistics()
    {
        return $this->sampleRepository->getSampleStatistics();
    }

    /**
     * Get evaluation statistics
     */
    public function getEvaluationStatistics()
    {
        return $this->sampleRepository->getEvaluationStatistics();
    }

    /**
     * Get samples by seller
     */
    public function getSamplesBySeller(int $sellerId)
    {
        return $this->sampleRepository->getSamplesBySeller($sellerId);
    }

    /**
     * Search samples
     */
    public function searchSamples(string $query)
    {
        return $this->sampleRepository->searchSamples($query);
    }

    /**
     * Get recent samples
     */
    public function getRecentSamples(int $limit = 10)
    {
        return $this->sampleRepository->getRecentSamples($limit);
    }

    // Mobile App Methods
    /**
     * Get samples for mobile app
     */
    public function getSamplesForMobile(int $userId, array $filters = [])
    {
        return $this->sampleRepository->getSamplesForMobileList($userId, $filters);
    }

    /**
     * Get sample details for mobile app
     */
    public function getSampleDetailsForMobile(int $id)
    {
        return $this->sampleRepository->getSampleDetailsForMobile($id);
    }

    /**
     * Create sample via mobile app
     */
    public function createSampleViaMobile(array $data, int $userId)
    {
        $data['received_by'] = $userId;
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        
        return $this->createSample($data);
    }

    // Helper Methods
    /**
     * Validate evaluation scores
     */
    private function validateEvaluationScores(array $data)
    {
        $scores = ['aroma_score', 'liquor_score', 'appearance_score'];
        
        foreach ($scores as $score) {
            if (!isset($data[$score]) || $data[$score] < 0 || $data[$score] > 10) {
                throw new \Exception("Invalid {$score}. Score must be between 0 and 10.");
            }
        }
    }

    /**
     * Calculate overall score
     */
    private function calculateOverallScore(float $aroma, float $liquor, float $appearance): float
    {
        return round(($aroma + $liquor + $appearance) / 3, 1);
    }

    /**
     * Get available tea grades
     */
    public function getAvailableTeaGrades(): array
    {
        return [
            'BP' => 'Broken Pekoe',
            'BOP' => 'Broken Orange Pekoe',
            'BOPF' => 'Broken Orange Pekoe Fannings',
            'PD' => 'Pekoe Dust',
            'Dust' => 'Dust Grade',
            'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe',
            'TGFOP' => 'Tippy Golden Flowery Orange Pekoe',
            'GFOP' => 'Golden Flowery Orange Pekoe',
            'FOP' => 'Flowery Orange Pekoe',
            'OP' => 'Orange Pekoe',
            'Pekoe' => 'Pekoe'
        ];
    }
}