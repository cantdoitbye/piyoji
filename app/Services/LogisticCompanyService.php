<?php

namespace App\Services;

use App\Models\LogisticCompany;
use App\Repositories\Interfaces\LogisticCompanyRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Validation\ValidationException;

class LogisticCompanyService implements BaseServiceInterface
{
    protected $logisticRepository;

    public function __construct(LogisticCompanyRepositoryInterface $logisticRepository)
    {
        $this->logisticRepository = $logisticRepository;
    }

    public function index(array $filters = [])
    {
        return $this->logisticRepository->getWithFilters($filters);
    }

    public function show(int $id)
    {
        return $this->logisticRepository->find($id);
    }

    public function store(array $data)
    {
        // Validate unique fields
        $this->validateUniqueFields($data);
        
        // Process arrays if they are strings
        if (isset($data['supported_routes']) && is_string($data['supported_routes'])) {
            $data['supported_routes'] = array_map('trim', explode(',', $data['supported_routes']));
        }

        if (isset($data['supported_regions']) && is_string($data['supported_regions'])) {
            $data['supported_regions'] = array_map('trim', explode(',', $data['supported_regions']));
        }

        return $this->logisticRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        // Validate unique fields excluding current record
        $this->validateUniqueFields($data, $id);
        
        // Process arrays if they are strings
        if (isset($data['supported_routes']) && is_string($data['supported_routes'])) {
            $data['supported_routes'] = array_map('trim', explode(',', $data['supported_routes']));
        }

        if (isset($data['supported_regions']) && is_string($data['supported_regions'])) {
            $data['supported_regions'] = array_map('trim', explode(',', $data['supported_regions']));
        }

        return $this->logisticRepository->update($id, $data);
    }

    public function destroy(int $id)
    {
        $logisticCompany = $this->logisticRepository->find($id);
        
        if (!$logisticCompany) {
            throw new \Exception('Logistic company not found');
        }

        // Check if logistic company has any active dispatches
        // Add business logic here if needed when dispatch modules are implemented
        
        return $this->logisticRepository->delete($id);
    }

    public function search(string $query)
    {
        return $this->logisticRepository->searchLogisticCompanies($query);
    }

    public function getForSelect()
    {
        return $this->logisticRepository->getActiveLogisticCompaniesList();
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->logisticRepository->updateStatus($id, $status);
    }

    public function getStatistics()
    {
        return $this->logisticRepository->getLogisticCompanyStatistics();
    }

    public function getCompaniesByRegion(string $region)
    {
        return $this->logisticRepository->getLogisticCompaniesByRegion($region);
    }

    public function getCompaniesByRoute(string $route)
    {
        return $this->logisticRepository->getLogisticCompaniesByRoute($route);
    }

    public function getCompaniesByState(string $state)
    {
        return $this->logisticRepository->getLogisticCompaniesByState($state);
    }

    public function getCompaniesByPricingType(string $pricingType)
    {
        return $this->logisticRepository->getByPricingType($pricingType);
    }

    public function getActiveCompaniesByRegionAndRoute(string $region = null, string $route = null)
    {
        return $this->logisticRepository->getActiveCompaniesByRegionAndRoute($region, $route);
    }

    public function bulkUpdateStatus(array $ids, bool $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->logisticRepository->updateStatus($id, $status)) {
                $updated++;
            }
        }
        return $updated;
    }

    public function exportData(array $filters = [])
    {
        $companies = $this->logisticRepository->getWithFilters($filters);
        
        return $companies->map(function ($company) {
            return [
                'ID' => $company->id,
                'Company Name' => $company->company_name,
                'Contact Person' => $company->contact_person,
                'Email' => $company->email,
                'Phone' => $company->phone,
                'Address' => $company->full_address,
                'Supported Routes' => $company->supported_routes_text,
                'Supported Regions' => $company->supported_regions_text,
                'Pricing Type' => $company->pricing_type_text,
                'Formatted Pricing' => $company->formatted_pricing,
                'GSTIN' => $company->gstin,
                'PAN' => $company->pan,
                'Status' => $company->status_text,
                'Created At' => $company->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function calculateShippingCost(int $companyId, $weight = null, $distance = null)
    {
        $company = $this->logisticRepository->find($companyId);
        
        if (!$company) {
            throw new \Exception('Logistic company not found');
        }

        return $company->calculatePricing($weight, $distance);
    }

    protected function validateUniqueFields(array $data, int $excludeId = null)
    {
        $errors = [];

        if (isset($data['email']) && $this->logisticRepository->checkEmailExists($data['email'], $excludeId)) {
            $errors['email'] = ['The email has already been taken.'];
        }

        if (isset($data['gstin']) && $data['gstin'] && $this->logisticRepository->checkGstinExists($data['gstin'], $excludeId)) {
            $errors['gstin'] = ['The GSTIN has already been taken.'];
        }

        if (isset($data['pan']) && $data['pan'] && $this->logisticRepository->checkPanExists($data['pan'], $excludeId)) {
            $errors['pan'] = ['The PAN has already been taken.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getRouteOptions()
    {
        return LogisticCompany::COMMON_ROUTES;
    }

    public function getRegionOptions()
    {
        return LogisticCompany::COMMON_REGIONS;
    }

    public function getPricingTypeOptions()
    {
        return LogisticCompany::getPricingTypeOptions();
    }

    public function getStatusOptions()
    {
        return LogisticCompany::getStatusOptions();
    }

    public function validatePricingData(array $data)
    {
        $errors = [];

        switch ($data['pricing_type'] ?? '') {
            case LogisticCompany::PRICING_TYPE_PER_KG:
                if (empty($data['per_kg_rate']) || $data['per_kg_rate'] <= 0) {
                    $errors['per_kg_rate'] = ['Per kg rate is required and must be greater than 0.'];
                }
                break;

            case LogisticCompany::PRICING_TYPE_PER_KM:
                if (empty($data['per_km_rate']) || $data['per_km_rate'] <= 0) {
                    $errors['per_km_rate'] = ['Per km rate is required and must be greater than 0.'];
                }
                break;

            case LogisticCompany::PRICING_TYPE_FLAT_RATE:
                if (empty($data['base_rate']) || $data['base_rate'] <= 0) {
                    $errors['base_rate'] = ['Base rate is required and must be greater than 0.'];
                }
                break;

            case LogisticCompany::PRICING_TYPE_CUSTOM:
                if (empty($data['pricing_structure'])) {
                    $errors['pricing_structure'] = ['Pricing structure is required for custom pricing.'];
                }
                break;
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}