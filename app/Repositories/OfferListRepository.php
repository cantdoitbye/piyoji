<?php

namespace App\Repositories;

use App\Models\OfferList;
use App\Repositories\Interfaces\OfferListRepositoryInterface;

class OfferListRepository extends BaseRepository implements OfferListRepositoryInterface
{
    public function __construct(OfferList $model)
    {
        parent::__construct($model);
    }

    public function getByGarden(int $gardenId)
    {
        return $this->model->byGarden($gardenId)
            ->with('garden')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getByGrade(string $grade)
    {
        return $this->model->byGrade($grade)
            ->with('garden')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getByType(string $type)
    {
        return $this->model->byType($type)
            ->with('garden')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate)
    {
        return $this->model->byDateRange($startDate, $endDate)
            ->with('garden')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function searchOffers(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('garden_name', 'LIKE', "%{$query}%")
              ->orWhere('grade', 'LIKE', "%{$query}%")
              ->orWhere('awr_no', 'LIKE', "%{$query}%")
              ->orWhere('key', 'LIKE', "%{$query}%")
              ->orWhereHas('garden', function($subQ) use ($query) {
                  $subQ->where('garden_name', 'LIKE', "%{$query}%");
              });
        })->with('garden')->get();
    }

    public function getOfferStatistics()
    {
        return [
            'total' => $this->model->count(),
            'current_month' => $this->model->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)->count(),
            'total_weight' => $this->model->sum('ttl_kgs'),
            'unique_gardens' => $this->model->distinct('garden_id')->count('garden_id'),
            'by_type' => $this->model->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')->pluck('count', 'type')->toArray(),
            'by_grade' => $this->model->selectRaw('grade, COUNT(*) as count')
                ->groupBy('grade')->orderBy('count', 'desc')
                ->limit(5)->pluck('count', 'grade')->toArray()
        ];
    }
}