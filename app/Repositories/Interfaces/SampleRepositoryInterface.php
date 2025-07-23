<?php

namespace App\Repositories\Interfaces;

interface SampleRepositoryInterface extends BaseRepositoryInterface
{
    // Sample Receiving (Module 2.1)
    public function getActiveSamplesList();
    
    public function getSamplesBySeller(int $sellerId);
    
    public function getSamplesByStatus(string $status);
    
    public function searchSamples(string $query);
    
    public function getSampleWithDetails($id);
    
    public function checkSampleIdExists(string $sampleId, int $excludeId = null);
    
    public function checkBatchIdExists(string $batchId, int $sellerId, int $excludeId = null);
    
    public function bulkCreateSamples(array $samples);
    
    // Sample Evaluation (Module 2.2)
    public function getPendingEvaluationSamples();
    
    public function getEvaluatedSamples();
    
    public function getApprovedSamples();
    
    public function getRejectedSamples();
    
    public function getTopScoringSamples(float $minScore = 8.0);
    
    public function getSamplesByScoreRange(float $minScore, float $maxScore);
    
    public function updateEvaluationStatus(int $id, string $status);
    
    public function saveSampleEvaluation(int $id, array $evaluationData);
    
    public function getSamplesForTastingReport(array $filters = []);
    
    // Statistics and Reports
    public function getSampleStatistics();
    
    public function getEvaluationStatistics();
    
    public function getSamplesByDateRange(string $startDate, string $endDate);
    
    public function getSellerSampleStatistics(int $sellerId);
    
    public function getMonthlyReceivingReport(int $year, int $month = null);
    
    public function getEvaluationPerformanceReport(int $userId = null);
    
    // Filter and Search
    public function getWithFilters(array $filters = []);
    
    public function getRecentSamples(int $limit = 10);
    
    public function getSamplesByUser(int $userId, string $type = 'received'); // received, evaluated
    
    // Mobile App specific
    public function getSamplesForMobileList(int $userId, array $filters = []);
    
    public function getSampleDetailsForMobile(int $id);
}