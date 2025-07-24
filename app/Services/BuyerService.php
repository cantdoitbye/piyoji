<?php

namespace App\Services;

use App\Models\Buyer;
use App\Repositories\Interfaces\BuyerRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Validation\ValidationException;

class BuyerService implements BaseServiceInterface
{
    protected $buyerRepository;

    public function __construct(BuyerRepositoryInterface $buyerRepository)
    {
        $this->buyerRepository = $buyerRepository;
    }

    public function index(array $filters = [])
    {
        return $this->buyerRepository->getWithFilters($filters);
    }

      public function getActiveBuyers()
    {
        return $this->buyerRepository->getActiveBuyersList();
    }

    public function show(int $id)
    {
        return $this->buyerRepository->find($id);
    }

    public function store(array $data)
    {
        // Validate unique fields
        $this->validateUniqueFields($data);
        
        // Process preferred tea grades if it's a string
        if (isset($data['preferred_tea_grades']) && is_string($data['preferred_tea_grades'])) {
            $data['preferred_tea_grades'] = array_map('trim', explode(',', $data['preferred_tea_grades']));
        }

        // Copy billing address to shipping if same_as_billing is checked
        if (isset($data['same_as_billing']) && $data['same_as_billing']) {
            $data['shipping_address'] = $data['billing_address'];
            $data['shipping_city'] = $data['billing_city'];
            $data['shipping_state'] = $data['billing_state'];
            $data['shipping_pincode'] = $data['billing_pincode'];
        }

        unset($data['same_as_billing']);

        return $this->buyerRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        // Validate unique fields excluding current record
        $this->validateUniqueFields($data, $id);
        
        // Process preferred tea grades if it's a string
        if (isset($data['preferred_tea_grades']) && is_string($data['preferred_tea_grades'])) {
            $data['preferred_tea_grades'] = array_map('trim', explode(',', $data['preferred_tea_grades']));
        }

        // Copy billing address to shipping if same_as_billing is checked
        if (isset($data['same_as_billing']) && $data['same_as_billing']) {
            $data['shipping_address'] = $data['billing_address'];
            $data['shipping_city'] = $data['billing_city'];
            $data['shipping_state'] = $data['billing_state'];
            $data['shipping_pincode'] = $data['billing_pincode'];
        }

        unset($data['same_as_billing']);

        return $this->buyerRepository->update($id, $data);
    }

    public function destroy(int $id)
    {
        $buyer = $this->buyerRepository->find($id);
        
        if (!$buyer) {
            throw new \Exception('Buyer not found');
        }

        // Check if buyer has any active orders or feedback
        // Add business logic here if needed
        
        return $this->buyerRepository->delete($id);
    }

    public function search(string $query)
    {
        return $this->buyerRepository->searchBuyers($query);
    }

    public function getForSelect()
    {
        return $this->buyerRepository->getActiveBuyersList();
    }

    public function updateStatus(int $id, bool $status)
    {
        return $this->buyerRepository->updateStatus($id, $status);
    }

    public function getStatistics()
    {
        return $this->buyerRepository->getBuyerStatistics();
    }

    public function getBuyersByType(string $type)
    {
        return $this->buyerRepository->getBuyersByType($type);
    }

    public function getBuyersByTeaGrade(string $grade)
    {
        return $this->buyerRepository->getBuyersByTeaGrade($grade);
    }

    public function getBuyerWithFeedbacks(int $id)
    {
        return $this->buyerRepository->getBuyerWithFeedbacks($id);
    }

    public function bulkUpdateStatus(array $ids, bool $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            if ($this->buyerRepository->updateStatus($id, $status)) {
                $updated++;
            }
        }
        return $updated;
    }

    public function exportData(array $filters = [])
    {
        // This would handle export functionality
        $buyers = $this->buyerRepository->getWithFilters($filters);
        
        return $buyers->map(function ($buyer) {
            return [
                'ID' => $buyer->id,
                'Buyer Name' => $buyer->buyer_name,
                'Type' => $buyer->buyer_type_text,
                'Contact Person' => $buyer->contact_person,
                'Email' => $buyer->email,
                'Phone' => $buyer->phone,
                'Billing City' => $buyer->billing_city,
                'Billing State' => $buyer->billing_state,
                'Shipping City' => $buyer->shipping_city,
                'Shipping State' => $buyer->shipping_state,
                'Preferred Tea Grades' => $buyer->preferred_tea_grades_text,
                'Status' => $buyer->status_text,
                'Created At' => $buyer->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    protected function validateUniqueFields(array $data, int $excludeId = null)
    {
        $errors = [];

        if (isset($data['email']) && $this->buyerRepository->checkEmailExists($data['email'], $excludeId)) {
            $errors['email'] = ['The email has already been taken.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getBuyerTypeOptions()
    {
        return Buyer::BUYER_TYPES;
    }

    public function getStatusOptions()
    {
        return [
            Buyer::STATUS_ACTIVE => 'Active',
            Buyer::STATUS_INACTIVE => 'Inactive'
        ];
    }

    public function getTeaGradeOptions()
    {
        return [
            'BP' => 'Broken Pekoe',
            'BOP' => 'Broken Orange Pekoe',
            'PD' => 'Pekoe Dust',
            'Dust' => 'Dust',
            'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe',
            'TGFOP' => 'Tippy Golden Flowery Orange Pekoe',
            'GFOP' => 'Golden Flowery Orange Pekoe',
            'FOP' => 'Flowery Orange Pekoe',
            'OP' => 'Orange Pekoe',
            'PEKOE' => 'Pekoe'
        ];
    }
}