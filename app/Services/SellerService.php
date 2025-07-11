<?php

namespace App\Services;

use App\Models\Seller;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Validation\ValidationException;

class SellerService implements BaseServiceInterface
{
    protected $sellerRepository;

    public function __construct(SellerRepositoryInterface $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }

    public function index(array $filters = [])
    {
        return $this->sellerRepository->getWithFilters($filters);
    }

    public function show(int $id)
    {
        return $this->sellerRepository->find($id);
    }

    public function store(array $data)
    {
        // Validate unique fields
        $this->validateUniqueFields($data);
        
        // Process tea grades if it's a string
        if (isset($data['tea_grades']) && is_string($data['tea_grades'])) {
            $data['tea_grades'] = array_map('trim', explode(',', $data['tea_grades']));
        }

        return $this->sellerRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        // Validate unique fields excluding current record
        $this->validateUniqueFields($data, $id);
        
        // Process tea grades if it's a string
        if (isset($data['tea_grades']) && is_string($data['tea_grades'])) {
            $data['tea_grades'] = array_map('trim', explode(',', $data['tea_grades']));
        }

        return $this->sellerRepository->update($id, $data);
    }

    public function destroy(int $id)
    {
        $seller = $this->sellerRepository->find($id);
        
        if (!$seller) {
            throw new \Exception('Seller not found');
        }

        // Check if seller has any active contracts or samples
        // Add business logic here if needed
        
        return $this->sellerRepository->delete($id);
    }

    public function search(string $query)
    {
        return $this->sellerRepository->searchSellers($query);
    }

    public function getForSelect()
    {
        return $this->sellerRepository->getActiveSellersList();
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->sellerRepository->updateStatus($id, $status);
    }

    public function getStatistics()
    {
        return $this->sellerRepository->getSellerStatistics();
    }

    public function getSellersByTeaGrade(string $grade)
    {
        return $this->sellerRepository->getSellersByTeaGrade($grade);
    }

    public function getSellerWithContracts(int $id)
    {
        return $this->sellerRepository->getSellerWithContracts($id);
    }

    public function bulkUpdateStatus(array $ids, bool $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->sellerRepository->updateStatus($id, $status)) {
                $updated++;
            }
        }
        return $updated;
    }

    public function exportData(array $filters = [])
    {
        // This would handle export functionality
        $sellers = $this->sellerRepository->getWithFilters($filters);
        
        return $sellers->map(function ($seller) {
            return [
                'ID' => $seller->id,
                'Seller Name' => $seller->seller_name,
                'Tea Estate' => $seller->tea_estate_name,
                'Contact Person' => $seller->contact_person,
                'Email' => $seller->email,
                'Phone' => $seller->phone,
                'City' => $seller->city,
                'State' => $seller->state,
                'GSTIN' => $seller->gstin,
                'PAN' => $seller->pan,
                'Tea Grades' => $seller->tea_grades_text,
                'Status' => $seller->status_text,
                'Created At' => $seller->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    protected function validateUniqueFields(array $data, int $excludeId = null)
    {
        $errors = [];

        if (isset($data['email']) && $this->sellerRepository->checkEmailExists($data['email'], $excludeId)) {
            $errors['email'] = ['The email has already been taken.'];
        }

        if (isset($data['gstin']) && $this->sellerRepository->checkGstinExists($data['gstin'], $excludeId)) {
            $errors['gstin'] = ['The GSTIN has already been taken.'];
        }

        if (isset($data['pan']) && $this->sellerRepository->checkPanExists($data['pan'], $excludeId)) {
            $errors['pan'] = ['The PAN has already been taken.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getTeaGradeOptions()
    {
        return Seller::TEA_GRADES;
    }

    public function getStatusOptions()
    {
        return [
            Seller::STATUS_ACTIVE => 'Active',
            Seller::STATUS_INACTIVE => 'Inactive'
        ];
    }
}