<?php

namespace App\Repositories;

use App\Models\LogisticCompany;
use App\Repositories\Interfaces\LogisticCompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LogisticCompanyRepository extends BaseRepository implements LogisticCompanyRepositoryInterface
{
    public function __construct(LogisticCompany $model)
    {
        parent::__construct($model);
    }

    public function getActiveLogisticCompaniesList()
    {
        return $this->model->active()
            ->select('id', 'company_name', 'contact_person', 'phone', 'supported_regions', 'pricing_type')
            ->orderBy('company_name')
            ->get();
    }

    public function getLogisticCompaniesByRegion(string $region)
    {
        return $this->model->active()
            ->byRegion($region)
            ->get();
    }

    public function getLogisticCompaniesByRoute(string $route)
    {
        return $this->model->active()
            ->byRoute($route)
            ->get();
    }

    public function getLogisticCompaniesByState(string $state)
    {
        return $this->model->active()
            ->byState($state)
            ->get();
    }

    public function searchLogisticCompanies(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('company_name', 'LIKE', "%{$query}%")
              ->orWhere('contact_person', 'LIKE', "%{$query}%")
              ->orWhere('email', 'LIKE', "%{$query}%")
              ->orWhere('phone', 'LIKE', "%{$query}%")
              ->orWhere('city', 'LIKE', "%{$query}%")
              ->orWhere('state', 'LIKE', "%{$query}%");
        })->get();
    }

    public function getLogisticCompanyStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->active()->count(),
            'inactive' => $this->model->inactive()->count(),
            'per_kg_pricing' => $this->model->where('pricing_type', LogisticCompany::PRICING_TYPE_PER_KG)->count(),
            'per_km_pricing' => $this->model->where('pricing_type', LogisticCompany::PRICING_TYPE_PER_KM)->count(),
            'flat_rate_pricing' => $this->model->where('pricing_type', LogisticCompany::PRICING_TYPE_FLAT_RATE)->count(),
            'custom_pricing' => $this->model->where('pricing_type', LogisticCompany::PRICING_TYPE_CUSTOM)->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function getLogisticCompaniesByStatus(bool $status)
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

    public function checkGstinExists(string $gstin, int $excludeId = null)
    {
        if (!$gstin) {
            return false;
        }

        $query = $this->model->where('gstin', $gstin);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function checkPanExists(string $pan, int $excludeId = null)
    {
        if (!$pan) {
            return false;
        }

        $query = $this->model->where('pan', $pan);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    public function getByPricingType(string $pricingType)
    {
        return $this->model->where('pricing_type', $pricingType)
            ->active()
            ->orderBy('company_name')
            ->get();
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->query();

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('company_name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('contact_person', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('email', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('phone', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('city', 'LIKE', "%{$filters['search']}%");
            });
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        // Pricing type filter
        if (!empty($filters['pricing_type'])) {
            $query->where('pricing_type', $filters['pricing_type']);
        }

        // State filter
        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Region filter
        if (!empty($filters['region'])) {
            $query->whereJsonContains('supported_regions', $filters['region']);
        }

        // Route filter
        if (!empty($filters['route'])) {
            $query->whereJsonContains('supported_routes', $filters['route']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('company_name')
                     ->paginate($perPage);
    }

    public function getActiveCompaniesByRegionAndRoute(string $region = null, string $route = null)
    {
        $query = $this->model->active();

        if ($region) {
            $query->byRegion($region);
        }

        if ($route) {
            $query->byRoute($route);
        }

        return $query->orderBy('company_name')->get();
    }
}