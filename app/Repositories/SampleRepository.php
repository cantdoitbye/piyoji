<?php

namespace App\Repositories;

use App\Models\Sample;
use App\Repositories\Interfaces\SampleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class SampleRepository extends BaseRepository implements SampleRepositoryInterface
{
    public function __construct(Sample $model)
    {
        parent::__construct($model);
    }

    // Sample Receiving (Module 2.1)
    public function getActiveSamplesList()
    {
        return $this->model->with(['seller', 'receivedBy'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSamplesBySeller(int $sellerId)
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where('seller_id', $sellerId)
            ->active()
            ->orderBy('arrival_date', 'desc')
            ->get();
    }

    public function getSamplesByStatus(string $status)
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where('status', $status)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function searchSamples(string $query)
    {
        return $this->model->with(['seller', 'receivedBy'])
            ->where(function ($q) use ($query) {
                $q->where('sample_name', 'like', "%{$query}%")
                  ->orWhere('sample_id', 'like', "%{$query}%")
                  ->orWhere('batch_id', 'like', "%{$query}%")
                  ->orWhereHas('seller', function ($sq) use ($query) {
                      $sq->where('seller_name', 'like', "%{$query}%")
                        ->orWhere('tea_estate_name', 'like', "%{$query}%");
                  });
            })
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSampleWithDetails(int $id)
    {
        return $this->model->with([
            'seller',
            'receivedBy',
            'evaluatedBy',
            'createdBy',
            'updatedBy'
        ])->find($id);
    }

    public function checkSampleIdExists(string $sampleId, int $excludeId = null)
    {
        $query = $this->model->where('sample_id', $sampleId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function checkBatchIdExists(string $batchId, int $sellerId, int $excludeId = null)
    {
        $query = $this->model->where('batch_id', $batchId)
            ->where('seller_id', $sellerId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function bulkCreateSamples(array $samples)
    {
        try {
            return $this->model->insert($samples);
        } catch (\Exception $e) {
            throw new \Exception('Failed to bulk create samples: ' . $e->getMessage());
        }
    }

    // Sample Evaluation (Module 2.2)
    public function getPendingEvaluationSamples()
    {
        return $this->model->with(['seller', 'receivedBy'])
            ->where('evaluation_status', Sample::EVALUATION_PENDING)
            ->orWhere('status', Sample::STATUS_PENDING_EVALUATION)
            ->active()
            ->orderBy('arrival_date', 'asc')
            ->get();
    }

    public function getEvaluatedSamples()
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->active()
            ->orderBy('evaluated_at', 'desc')
            ->get();
    }

    public function getApprovedSamples()
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where('status', Sample::STATUS_APPROVED)
            ->active()
            ->orderBy('overall_score', 'desc')
            ->get();
    }

    public function getRejectedSamples()
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where('status', Sample::STATUS_REJECTED)
            ->active()
            ->orderBy('evaluated_at', 'desc')
            ->get();
    }

    public function getTopScoringSamples(float $minScore = 8.0)
    {
        return $this->model->with(['seller', 'evaluatedBy'])
            ->where('overall_score', '>=', $minScore)
            ->where('status', Sample::STATUS_APPROVED)
            ->active()
            ->orderBy('overall_score', 'desc')
            ->get();
    }

    public function getSamplesByScoreRange(float $minScore, float $maxScore)
    {
        return $this->model->with(['seller', 'evaluatedBy'])
            ->whereBetween('overall_score', [$minScore, $maxScore])
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->active()
            ->orderBy('overall_score', 'desc')
            ->get();
    }

    public function updateEvaluationStatus(int $id, string $status)
    {
        return $this->model->where('id', $id)->update([
            'evaluation_status' => $status,
            'updated_at' => now()
        ]);
    }

    public function saveSampleEvaluation(int $id, array $evaluationData)
    {
        return $this->model->where('id', $id)->update(array_merge($evaluationData, [
            'evaluation_status' => Sample::EVALUATION_COMPLETED,
            'evaluated_at' => now(),
            'updated_at' => now()
        ]));
    }

    public function getSamplesForTastingReport(array $filters = [])
    {
        $query = $this->model->with(['seller', 'evaluatedBy'])
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->active();

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (isset($filters['min_score'])) {
            $query->where('overall_score', '>=', $filters['min_score']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('evaluated_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('evaluated_at', '<=', $filters['end_date']);
        }

        return $query->orderBy('overall_score', 'desc')->get();
    }

    // Statistics and Reports
    public function getSampleStatistics()
    {
        $stats = [];
        
        $stats['total'] = $this->model->active()->count();
        $stats['received'] = $this->model->active()->where('status', Sample::STATUS_RECEIVED)->count();
        $stats['pending_evaluation'] = $this->model->active()->where('evaluation_status', Sample::EVALUATION_PENDING)->count();
        $stats['evaluated'] = $this->model->active()->where('evaluation_status', Sample::EVALUATION_COMPLETED)->count();
        $stats['approved'] = $this->model->active()->where('status', Sample::STATUS_APPROVED)->count();
        $stats['rejected'] = $this->model->active()->where('status', Sample::STATUS_REJECTED)->count();
        $stats['assigned_to_buyers'] = $this->model->active()->where('status', Sample::STATUS_ASSIGNED_TO_BUYERS)->count();
        
        // This month statistics
        $thisMonth = Carbon::now()->startOfMonth();
        $stats['this_month'] = $this->model->active()->where('created_at', '>=', $thisMonth)->count();
        
        // Average scores
        $avgScores = $this->model->active()
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->selectRaw('AVG(aroma_score) as avg_aroma, AVG(liquor_score) as avg_liquor, AVG(appearance_score) as avg_appearance, AVG(overall_score) as avg_overall')
            ->first();
            
        $stats['avg_scores'] = [
            'aroma' => round($avgScores->avg_aroma ?? 0, 1),
            'liquor' => round($avgScores->avg_liquor ?? 0, 1),
            'appearance' => round($avgScores->avg_appearance ?? 0, 1),
            'overall' => round($avgScores->avg_overall ?? 0, 1)
        ];

        return $stats;
    }

    public function getEvaluationStatistics()
    {
        $stats = [];
        
        $total = $this->model->active()->count();
        $evaluated = $this->model->active()->where('evaluation_status', Sample::EVALUATION_COMPLETED)->count();
        
        $stats['evaluation_rate'] = $total > 0 ? round(($evaluated / $total) * 100, 1) : 0;
        $stats['pending_count'] = $this->model->active()->where('evaluation_status', Sample::EVALUATION_PENDING)->count();
        $stats['approved_count'] = $this->model->active()->where('status', Sample::STATUS_APPROVED)->count();
        $stats['rejected_count'] = $this->model->active()->where('status', Sample::STATUS_REJECTED)->count();
        
        // Top performers
        $stats['top_samples'] = $this->model->active()
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->orderBy('overall_score', 'desc')
            ->limit(5)
            ->get(['id', 'sample_id', 'sample_name', 'overall_score']);

        return $stats;
    }

    public function getSamplesByDateRange(string $startDate, string $endDate)
    {
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->whereBetween('arrival_date', [$startDate, $endDate])
            ->active()
            ->orderBy('arrival_date', 'desc')
            ->get();
    }

    public function getSellerSampleStatistics(int $sellerId)
    {
        $stats = [];
        
        $stats['total'] = $this->model->active()->where('seller_id', $sellerId)->count();
        $stats['evaluated'] = $this->model->active()->where('seller_id', $sellerId)->where('evaluation_status', Sample::EVALUATION_COMPLETED)->count();
        $stats['approved'] = $this->model->active()->where('seller_id', $sellerId)->where('status', Sample::STATUS_APPROVED)->count();
        $stats['rejected'] = $this->model->active()->where('seller_id', $sellerId)->where('status', Sample::STATUS_REJECTED)->count();
        
        // Average score for this seller
        $avgScore = $this->model->active()
            ->where('seller_id', $sellerId)
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED)
            ->avg('overall_score');
            
        $stats['avg_score'] = round($avgScore ?? 0, 1);

        return $stats;
    }

    public function getMonthlyReceivingReport(int $year, int $month = null)
    {
        $query = $this->model->active()
            ->whereYear('arrival_date', $year);
            
        if ($month) {
            $query->whereMonth('arrival_date', $month);
        }
        
        return $query->with(['seller'])
            ->selectRaw('DATE(arrival_date) as date, COUNT(*) as count, seller_id')
            ->groupBy('date', 'seller_id')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getEvaluationPerformanceReport(int $userId = null)
    {
        $query = $this->model->active()
            ->where('evaluation_status', Sample::EVALUATION_COMPLETED);
            
        if ($userId) {
            $query->where('evaluated_by', $userId);
        }
        
        return $query->with(['evaluatedBy'])
            ->selectRaw('evaluated_by, COUNT(*) as total_evaluated, AVG(overall_score) as avg_score, DATE(evaluated_at) as date')
            ->groupBy('evaluated_by', 'date')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->active();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['evaluation_status'])) {
            $query->where('evaluation_status', $filters['evaluation_status']);
        }

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        if (isset($filters['received_by'])) {
            $query->where('received_by', $filters['received_by']);
        }

        if (isset($filters['evaluated_by'])) {
            $query->where('evaluated_by', $filters['evaluated_by']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('arrival_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('arrival_date', '<=', $filters['end_date']);
        }

        if (isset($filters['min_score'])) {
            $query->where('overall_score', '>=', $filters['min_score']);
        }

        if (isset($filters['max_score'])) {
            $query->where('overall_score', '<=', $filters['max_score']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sample_name', 'like', "%{$search}%")
                  ->orWhere('sample_id', 'like', "%{$search}%")
                  ->orWhere('batch_id', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    public function getRecentSamples(int $limit = 10)
    {
        return $this->model->with(['seller', 'receivedBy'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSamplesByUser(int $userId, string $type = 'received')
    {
        $column = $type === 'received' ? 'received_by' : 'evaluated_by';
        
        return $this->model->with(['seller', 'receivedBy', 'evaluatedBy'])
            ->where($column, $userId)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Mobile App specific
    public function getSamplesForMobileList(int $userId, array $filters = [])
    {
        $query = $this->model->with(['seller'])
            ->active();

        // Filter by user permissions or assignments
        if (isset($filters['user_samples_only']) && $filters['user_samples_only']) {
            $query->where(function ($q) use ($userId) {
                $q->where('received_by', $userId)
                  ->orWhere('evaluated_by', $userId)
                  ->orWhere('created_by', $userId);
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['evaluation_status'])) {
            $query->where('evaluation_status', $filters['evaluation_status']);
        }

        $perPage = $filters['per_page'] ?? 20;
        
        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSampleDetailsForMobile(int $id)
    {
        return $this->model->with([
            'seller:id,seller_name,tea_estate_name',
            'receivedBy:id,name',
            'evaluatedBy:id,name'
        ])->find($id);
    }
}