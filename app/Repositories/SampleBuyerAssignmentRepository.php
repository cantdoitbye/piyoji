<?php

namespace App\Repositories;

use App\Models\SampleBuyerAssignment;
use App\Repositories\Interfaces\SampleBuyerAssignmentRepositoryInterface;

class SampleBuyerAssignmentRepository extends BaseRepository implements SampleBuyerAssignmentRepositoryInterface
{
    public function __construct(SampleBuyerAssignment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get assignments for a specific sample
     */
    public function getAssignmentsBySample(int $sampleId)
    {
        return $this->model->with(['buyer', 'assignedBy'])
                          ->where('sample_id', $sampleId)
                          ->orderBy('assigned_at', 'desc')
                          ->get();
    }

    /**
     * Get assignments for a specific buyer
     */
    public function getAssignmentsByBuyer(int $buyerId)
    {
        return $this->model->with(['sample.seller', 'assignedBy'])
                          ->where('buyer_id', $buyerId)
                          ->orderBy('assigned_at', 'desc')
                          ->get();
    }

    /**
     * Get all assignments awaiting dispatch
     */
    public function getAwaitingDispatchAssignments()
    {
        return $this->model->with(['sample.seller', 'buyer', 'assignedBy'])
                          ->awaitingDispatch()
                          ->orderBy('assigned_at', 'asc')
                          ->get();
    }

    /**
     * Bulk create assignments for a sample
     */
    public function bulkAssignSample(int $sampleId, array $buyerAssignments)
    {
        $assignments = [];
        $now = now();

        foreach ($buyerAssignments as $assignment) {
            $assignments[] = [
                'sample_id' => $sampleId,
                'buyer_id' => $assignment['buyer_id'],
                'assignment_remarks' => $assignment['remarks'] ?? null,
                'dispatch_status' => SampleBuyerAssignment::STATUS_AWAITING_DISPATCH,
                'assigned_at' => $now,
                'assigned_by' => $assignment['assigned_by'],
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        return $this->model->insert($assignments);
    }

    /**
     * Check if sample is already assigned to buyer
     */
    public function checkExistingAssignment(int $sampleId, int $buyerId)
    {
        return $this->model->where('sample_id', $sampleId)
                          ->where('buyer_id', $buyerId)
                          ->exists();
    }

    /**
     * Get assignments by status
     */
    public function getAssignmentsByStatus(string $status)
    {
        return $this->model->with(['sample.seller', 'buyer', 'assignedBy'])
                          ->where('dispatch_status', $status)
                          ->orderBy('assigned_at', 'desc')
                          ->get();
    }

    /**
     * Update dispatch status
     */
    public function updateDispatchStatus(int $id, string $status, array $additionalData = [])
    {
        $updateData = array_merge([
            'dispatch_status' => $status,
            'updated_at' => now()
        ], $additionalData);

        return $this->update($id, $updateData);
    }

    /**
     * Get assignment statistics
     */
    public function getAssignmentStatistics()
    {
        return [
            'total_assignments' => $this->model->count(),
            'awaiting_dispatch' => $this->model->awaitingDispatch()->count(),
            'dispatched' => $this->model->dispatched()->count(),
            'delivered' => $this->model->delivered()->count(),
            'today_assignments' => $this->model->whereDate('assigned_at', today())->count()
        ];
    }
}