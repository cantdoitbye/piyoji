<?php

namespace App\Repositories;

use App\Models\Buyer;
use App\Repositories\Interfaces\BuyerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuyerRepository extends BaseRepository implements BuyerRepositoryInterface
{
    public function __construct(Buyer $model)
    {
        parent::__construct($model);
    }

    public function getActiveBuyersList()
    {
        return $this->model->active()
            ->select('id', 'buyer_name', 'buyer_type', 'email')
            ->orderBy('buyer_name')
            ->get();
    }

    public function getBuyersByType(string $type)
    {
        return $this->model->active()
            ->where('buyer_type', $type)
            ->orderBy('buyer_name')
            ->get();
    }

    public function searchBuyers(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('buyer_name', 'LIKE', "%{$query}%")
              ->orWhere('contact_person', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('billing_city', 'LIKE', "%{$query}%")
              ->orWhere('shipping_city', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getBuyerWithFeedbacks(int $id)
    {
        return $this->model->with(['feedbacks' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($id);
    }

    public function getBuyerStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'big_buyers' => $this->model->bigBuyers()->count(),
            'small_buyers' => $this->model->smallBuyers()->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getBuyersByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('buyer_name')
            ->get();
    }

    public function checkEmailExists(string $email, int $excludeId = null)
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function getBuyersByTeaGrade(string $grade)
    {
        return $this->model->active()
            ->whereJsonContains('preferred_tea_grades', $grade)
            ->get();
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->newQuery();

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('buyer_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('billing_city', 'LIKE', "%{$search}%")
                  ->orWhere('shipping_city', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['buyer_type']) && $filters['buyer_type']) {
            $query->where('buyer_type', $filters['buyer_type']);
        }

        if (isset($filters['tea_grade']) && $filters['tea_grade']) {
            $query->whereJsonContains('preferred_tea_grades', $filters['tea_grade']);
        }

        if (isset($filters['state']) && $filters['state']) {
            $query->where('billing_state', $filters['state'])
                  ->orWhere('shipping_state', $filters['state']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }
}