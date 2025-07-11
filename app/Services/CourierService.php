<?php

namespace App\Services;

use App\Models\CourierService as CourierModel;
use App\Repositories\Interfaces\CourierRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Validation\ValidationException;

class CourierService implements BaseServiceInterface
{
    protected $courierRepository;

    public function __construct(CourierRepositoryInterface $courierRepository)
    {
        $this->courierRepository = $courierRepository;
    }

    public function index(array $filters = [])
    {
        return $this->courierRepository->getWithFilters($filters);
    }

    public function show(int $id)
    {
        return $this->courierRepository->find($id);
    }

    public function store(array $data)
    {
        // Validate unique fields
        $this->validateUniqueFields($data);
        
        // Process service areas if it's a string
        if (isset($data['service_areas']) && is_string($data['service_areas'])) {
            $data['service_areas'] = array_map('trim', explode(',', $data['service_areas']));
        }

        return $this->courierRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        // Validate unique fields excluding current record
        $this->validateUniqueFields($data, $id);
        
        // Process service areas if it's a string
        if (isset($data['service_areas']) && is_string($data['service_areas'])) {
            $data['service_areas'] = array_map('trim', explode(',', $data['service_areas']));
        }

        // Don't update encrypted fields if they are empty (keep existing values)
        if (empty($data['api_token'])) {
            unset($data['api_token']);
        }
        if (empty($data['api_password'])) {
            unset($data['api_password']);
        }

        return $this->courierRepository->update($id, $data);
    }

    public function destroy(int $id)
    {
        $courier = $this->courierRepository->find($id);
        
        if (!$courier) {
            throw new \Exception('Courier service not found');
        }

        // Check if courier has any active shipments
        // Add business logic here if needed
        
        return $this->courierRepository->delete($id);
    }

    public function search(string $query)
    {
        return $this->courierRepository->searchCouriers($query);
    }

    public function getForSelect()
    {
        return $this->courierRepository->getActiveCouriersList();
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->courierRepository->updateStatus($id, $status);
    }

    public function getStatistics()
    {
        return $this->courierRepository->getCourierStatistics();
    }

    public function getCouriersByServiceArea(string $area)
    {
        return $this->courierRepository->getCouriersByServiceArea($area);
    }

    public function getCourierWithShipments(int $id)
    {
        return $this->courierRepository->getCourierWithShipments($id);
    }

    public function getCouriersWithApiIntegration()
    {
        return $this->courierRepository->getCouriersWithApiIntegration();
    }

    public function testApiConnection(int $id)
    {
        return $this->courierRepository->testApiConnection($id);
    }

    public function bulkUpdateStatus(array $ids, bool $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->courierRepository->updateStatus($id, $status)) {
                $updated++;
            }
        }
        return $updated;
    }

    public function exportData(array $filters = [])
    {
        // This would handle export functionality
        $couriers = $this->courierRepository->getWithFilters($filters);
        
        return $couriers->map(function ($courier) {
            return [
                'ID' => $courier->id,
                'Company Name' => $courier->company_name,
                'Contact Person' => $courier->contact_person,
                'Email' => $courier->email,
                'Phone' => $courier->phone,
                'Service Areas' => $courier->service_areas_text,
                'API Endpoint' => $courier->api_endpoint,
                'Has API Integration' => $courier->api_endpoint ? 'Yes' : 'No',
                'Status' => $courier->status_text,
                'Created At' => $courier->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    protected function validateUniqueFields(array $data, int $excludeId = null)
    {
        $errors = [];

        if (isset($data['email']) && $this->courierRepository->checkEmailExists($data['email'], $excludeId)) {
            $errors['email'] = ['The email has already been taken.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getServiceAreaOptions()
    {
        return CourierModel::COMMON_SERVICE_AREAS;
    }

    public function getStatusOptions()
    {
        return [
            CourierModel::STATUS_ACTIVE => 'Active',
            CourierModel::STATUS_INACTIVE => 'Inactive'
        ];
    }

    public function generateTrackingUrl(int $courierId, string $trackingNumber)
    {
        $courier = $this->courierRepository->find($courierId);
        
        if (!$courier) {
            throw new \Exception('Courier service not found');
        }

        return $courier->generateTrackingUrl($trackingNumber);
    }

    public function validateApiCredentials(array $data)
    {
        // Basic validation for API credentials
        $errors = [];

        if (!empty($data['api_endpoint']) && !filter_var($data['api_endpoint'], FILTER_VALIDATE_URL)) {
            $errors['api_endpoint'] = ['The API endpoint must be a valid URL.'];
        }

        if (!empty($data['webhook_url']) && !filter_var($data['webhook_url'], FILTER_VALIDATE_URL)) {
            $errors['webhook_url'] = ['The webhook URL must be a valid URL.'];
        }

        if (!empty($data['tracking_url_template']) && strpos($data['tracking_url_template'], '{tracking_number}') === false) {
            $errors['tracking_url_template'] = ['The tracking URL template must contain {tracking_number} placeholder.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return true;
    }
}