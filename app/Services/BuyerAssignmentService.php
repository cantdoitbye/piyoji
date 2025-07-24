<?php

namespace App\Services;

use App\Models\Sample;
use App\Models\SampleBuyerAssignment;
use App\Repositories\Interfaces\SampleRepositoryInterface;
use App\Repositories\Interfaces\SampleBuyerAssignmentRepositoryInterface;
use App\Repositories\Interfaces\BuyerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BuyerAssignmentService
{
    protected $sampleRepository;
    protected $assignmentRepository;
    protected $buyerRepository;

    public function __construct(
        SampleRepositoryInterface $sampleRepository,
        SampleBuyerAssignmentRepositoryInterface $assignmentRepository,
        BuyerRepositoryInterface $buyerRepository
    ) {
        $this->sampleRepository = $sampleRepository;
        $this->assignmentRepository = $assignmentRepository;
        $this->buyerRepository = $buyerRepository;
    }

    /**
     * Get samples ready for buyer assignment (Module 2.3)
     */
    public function getSamplesReadyForAssignment()
    {
        return $this->sampleRepository->getApprovedSamples()
                    ->filter(function ($sample) {
                        return !$sample->isAssignedToBuyers();
                    });
    }

    /**
     * Assign sample to multiple buyers
     */
    public function assignSampleToBuyers(int $sampleId, array $buyerData)
    {
        try {
            DB::beginTransaction();

            $sample = $this->sampleRepository->find($sampleId);
            if (!$sample) {
                throw new \Exception('Sample not found');
            }

            if ($sample->status !== Sample::STATUS_APPROVED) {
                throw new \Exception('Only approved samples can be assigned to buyers');
            }

            $assignments = [];
            $userId = Auth::id();

            foreach ($buyerData as $data) {
                // Validate buyer exists
                $buyer = $this->buyerRepository->find($data['buyer_id']);
                if (!$buyer) {
                    throw new \Exception("Buyer with ID {$data['buyer_id']} not found");
                }

                // Check if already assigned
                if ($this->assignmentRepository->checkExistingAssignment($sampleId, $data['buyer_id'])) {
                    throw new \Exception("Sample already assigned to buyer: {$buyer->buyer_name}");
                }

                $assignments[] = [
                    'buyer_id' => $data['buyer_id'],
                    'remarks' => $data['remarks'] ?? null,
                    'assigned_by' => $userId
                ];
            }

            // Create bulk assignments
            $this->assignmentRepository->bulkAssignSample($sampleId, $assignments);

            // Update sample status
            $this->sampleRepository->update($sampleId, [
                'status' => Sample::STATUS_ASSIGNED_TO_BUYERS,
                'updated_by' => $userId
            ]);

            DB::commit();

            return $this->sampleRepository->getSampleWithDetails($sampleId);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get assigned samples with buyer details
     */
    public function getAssignedSamples()
    {
        return $this->sampleRepository->getSamplesByStatus(Sample::STATUS_ASSIGNED_TO_BUYERS);
    }

    /**
     * Get assignments awaiting dispatch
     */
    public function getAssignmentsAwaitingDispatch()
    {
        return $this->assignmentRepository->getAwaitingDispatchAssignments();
    }

    /**
     * Update assignment dispatch status
     */
    public function updateDispatchStatus(int $assignmentId, string $status, array $additionalData = [])
    {
        try {
            DB::beginTransaction();

            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw new \Exception('Assignment not found');
            }

            // Add dispatch timestamp if status is dispatched
            if ($status === SampleBuyerAssignment::STATUS_DISPATCHED && !isset($additionalData['dispatched_at'])) {
                $additionalData['dispatched_at'] = now();
            }

            $this->assignmentRepository->updateDispatchStatus($assignmentId, $status, $additionalData);

            DB::commit();

            return $this->assignmentRepository->find($assignmentId);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove buyer assignment
     */
    public function removeAssignment(int $assignmentId)
    {
        try {
            DB::beginTransaction();

            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw new \Exception('Assignment not found');
            }

            if ($assignment->dispatch_status !== SampleBuyerAssignment::STATUS_AWAITING_DISPATCH) {
                throw new \Exception('Cannot remove assignment that has been dispatched');
            }

            $sampleId = $assignment->sample_id;
            $this->assignmentRepository->delete($assignmentId);

            // Check if sample has any remaining assignments
            $remainingAssignments = $this->assignmentRepository->getAssignmentsBySample($sampleId);
            if ($remainingAssignments->isEmpty()) {
                // Update sample status back to approved
                $this->sampleRepository->update($sampleId, [
                    'status' => Sample::STATUS_APPROVED,
                    'updated_by' => Auth::id()
                ]);
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStatistics()
    {
        return $this->assignmentRepository->getAssignmentStatistics();
    }

    /**
     * Get sample assignments with details
     */
    public function getSampleAssignments(int $sampleId)
    {
        return $this->assignmentRepository->getAssignmentsBySample($sampleId);
    }
}