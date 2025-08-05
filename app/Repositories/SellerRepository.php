<?php

namespace App\Repositories;

use App\Models\Seller;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SellerRepository extends BaseRepository implements SellerRepositoryInterface
{
    public function __construct(Seller $model)
    {
        parent::__construct($model);
    }

    public function getActiveSellersList()
    {
        return $this->model->active()
            ->select('id', 'seller_name', 'tea_estate_name','tea_grades')
            ->orderBy('seller_name')
            ->get();
    }

    public function getSellersByTeaGrade(string $grade)
    {
        return $this->model->active()
            ->whereJsonContains('tea_grades', $grade)
            ->get();
    }

    public function searchSellers(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('seller_name', 'LIKE', "%{$query}%")
              ->orWhere('tea_estate_name', 'LIKE', "%{$query}%")
              ->orWhere('contact_person', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('city', 'LIKE', "%{$query}%")
              ->orWhere('gstin', 'LIKE', "%{$query}%")
              ->orWhere('pan', 'LIKE', "%{$query}%")
              ->orWhere('garden_name', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getSellerWithContracts(int $id)
    {
        return $this->model->with(['contracts' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($id);
    }

    public function getSellerStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getSellersByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('seller_name')
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

    public function checkGstinExists(string $gstin, int $excludeId = null)
    {
        $query = $this->model->where('gstin', $gstin);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function checkPanExists(string $pan, int $excludeId = null)
    {
        $query = $this->model->where('pan', $pan);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->newQuery();

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('seller_name', 'LIKE', "%{$search}%")
                  ->orWhere('tea_estate_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['tea_grade']) && $filters['tea_grade']) {
            $query->whereJsonContains('tea_grades', $filters['tea_grade']);
        }

        if (isset($filters['state']) && $filters['state']) {
            $query->where('state', $filters['state']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getSellersByGardenId(int $gardenId)
{
    return $this->model->active()
        ->whereJsonContains('garden_ids', $gardenId)
        ->orderBy('seller_name')
        ->get();
}

public function getSellerWithGardens(int $id)
{
    $seller = $this->model->find($id);
    if ($seller && $seller->garden_ids) {
        $seller->load(['selectedGardens']);
    }
    return $seller;
}
}