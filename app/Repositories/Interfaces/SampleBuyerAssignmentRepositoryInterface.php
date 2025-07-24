<?php

namespace App\Repositories\Interfaces;

interface SampleBuyerAssignmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get assignments for a specific sample
     */
    public function getAssignmentsBySample(int $sampleId);
    
    /**
     * Get assignments for a specific buyer
     */
    public function getAssignmentsByBuyer(int $buyerId);
    
    /**
     * Get all assignments awaiting dispatch
     */
    public function getAwaitingDispatchAssignments();
    
    /**
     * Bulk create assignments for a sample
     */
    public function bulkAssignSample(int $sampleId, array $buyerAssignments);
    
    /**
     * Check if sample is already assigned to buyer
     */
    public function checkExistingAssignment(int $sampleId, int $buyerId);
    
    /**
     * Get assignments by status
     */
    public function getAssignmentsByStatus(string $status);
    
    /**
     * Update dispatch status
     */
    public function updateDispatchStatus(int $id, string $status, array $additionalData = []);
    
    /**
     * Get assignment statistics
     */
    public function getAssignmentStatistics();
}