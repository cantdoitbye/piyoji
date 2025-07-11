<?php

namespace App\Repositories;

use App\Models\CourierService;
use App\Repositories\Interfaces\CourierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourierRepository extends BaseRepository implements CourierRepositoryInterface
{
    public function __construct(CourierService $model)
    {
        parent::__construct($model);
    }

    public function getActiveCouriersList()
    {
        return $this->model->active()
            ->select('id', 'company_name', 'contact_person', 'phone')
            ->orderBy('company_name')
            ->get();
    }

    public function getCouriersByServiceArea(string $area)
    {
        return $this->model->active()
            ->whereJsonContains('service_areas', $area)
            ->get();
    }

    public function searchCouriers(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('company_name', 'LIKE', "%{$query}%")
              ->orWhere('contact_person', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getCourierWithShipments(int $id)
    {
        return $this->model->with(['shipments' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->find($id);
    }

    public function getCourierStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'with_api' => $this->model->whereNotNull('api_endpoint')->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getCouriersByStatus(bool $status)
    {
        return $this->model->where('status', $status)
            ->orderBy('company_name')
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

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->newQuery();

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['service_area']) && $filters['service_area']) {
            $query->whereJsonContains('service_areas', $filters['service_area']);
        }

        if (isset($filters['has_api']) && $filters['has_api'] !== '') {
            if ($filters['has_api']) {
                $query->whereNotNull('api_endpoint');
            } else {
                $query->whereNull('api_endpoint');
            }
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getCouriersWithApiIntegration()
    {
        return $this->model->active()
            ->whereNotNull('api_endpoint')
            ->whereNotNull('api_token')
            ->get();
    }

    public function testApiConnection(int $id)
    {
        $courier = $this->find($id);
        
        if (!$courier || !$courier->api_endpoint) {
            return false;
        }

        try {
            // This would typically make an API call to test connectivity
            // For now, we'll just return true if API details exist
            return !empty($courier->getDecryptedApiToken());
        } catch (\Exception $e) {
            return false;
        }
    }

    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }
}