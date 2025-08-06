<?php

namespace App\Repositories;

use App\Models\BillingCompany;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BillingCompanyRepository
{
    protected $model;

    public function __construct(BillingCompany $model)
    {
        $this->model = $model;
    }

    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->with(['shippingAddresses', 'sellers', 'pocAssignments.poc']);

        // Search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('gstin', 'like', "%{$search}%")
                  ->orWhere('pan', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool)$filters['status']);
        }

        // Type filter
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // State filter
        if (isset($filters['state']) && !empty($filters['state'])) {
            $query->where('billing_state', $filters['state']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): ?BillingCompany
    {
        return $this->model->with([
            'shippingAddresses',
            'sellers',
            'pocAssignments.poc',
            'pocAssignments.seller'
        ])->find($id);
    }

    public function create(array $data): BillingCompany
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): BillingCompany
    {
        $billingCompany = $this->find($id);
        $billingCompany->update($data);
        return $billingCompany->fresh();
    }

    public function delete(int $id): bool
    {
        $billingCompany = $this->find($id);
        return $billingCompany ? $billingCompany->delete() : false;
    }

    public function updateStatus(int $id, bool $status): bool
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getByType(string $type): Collection
    {
        return $this->model->active()
                          ->byType($type)
                          ->select('id', 'company_name', 'contact_person', 'email', 'phone', 'type')
                          ->get();
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'by_type' => [
                'sellers' => $this->model->sellers()->count(),
                'buyers' => $this->model->buyers()->count(),
                'both' => $this->model->byType('both')->count(),
            ],
            'recent_additions' => $this->model->latest()->take(5)->get(['id', 'company_name', 'type', 'created_at'])
        ];
    }

    public function checkEmailExists(string $email, int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function getActiveList(): Collection
    {
        return $this->model->active()
                          ->select('id', 'company_name', 'type', 'contact_person', 'email')
                          ->orderBy('company_name')
                          ->get();
    }

    public function searchByName(string $search, int $limit = 10): Collection
    {
        return $this->model->active()
                          ->where('company_name', 'like', "%{$search}%")
                          ->select('id', 'company_name', 'type', 'contact_person')
                          ->limit($limit)
                          ->get();
    }

    public function getWithShippingAddresses(): Collection
    {
        return $this->model->active()
                          ->with('activeShippingAddresses')
                          ->whereIn('type', ['buyer', 'both'])
                          ->get();
    }

    public function getBySeller(int $sellerId): Collection
    {
        return $this->model->active()
                          ->whereHas('sellers', function($query) use ($sellerId) {
                              $query->where('seller_id', $sellerId);
                          })
                          ->with('sellers')
                          ->get();
    }

    public function getByPoc(int $pocId): Collection
    {
        return $this->model->active()
                          ->whereHas('pocAssignments', function($query) use ($pocId) {
                              $query->where('poc_id', $pocId);
                          })
                          ->with(['pocAssignments' => function($query) use ($pocId) {
                              $query->where('poc_id', $pocId);
                          }])
                          ->get();
    }
}