<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractItem;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractItemRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContractService implements BaseServiceInterface
{
    protected $contractRepository;
    protected $contractItemRepository;

    public function __construct(
        ContractRepositoryInterface $contractRepository,
        ContractItemRepositoryInterface $contractItemRepository
    ) {
        $this->contractRepository = $contractRepository;
        $this->contractItemRepository = $contractItemRepository;
    }

    public function index(array $filters = [])
    {
        return $this->contractRepository->getWithFilters($filters);
    }

    public function show(int $id)
    {
        return $this->contractRepository->getContractWithItems($id);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            // Validate contract data
            $this->validateContractData($data);
            
            // Generate contract number if not provided
            if (empty($data['contract_number'])) {
                $data['contract_number'] = Contract::generateContractNumber();
            }

            // Set created_by from authenticated user
            $data['created_by'] = auth()->id();

            // Create contract
            $contract = $this->contractRepository->create($data);

            // Create contract items if provided
            if (!empty($data['contract_items'])) {
                $this->createContractItems($contract->id, $data['contract_items']);
            }

            DB::commit();
            return $contract->fresh(['seller', 'contractItems']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            // Validate contract data
            $this->validateContractData($data, $id);
            
            // Set updated_by from authenticated user
            $data['updated_by'] = auth()->id();

            // Update contract
            $contract = $this->contractRepository->update($id, $data);

            // Update contract items if provided
            if (isset($data['contract_items'])) {
                $this->updateContractItems($id, $data['contract_items']);
            }

            DB::commit();
            return $this->contractRepository->getContractWithItems($id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        $contract = $this->contractRepository->find($id);
        
        if (!$contract) {
            throw new \Exception('Contract not found');
        }

        // Check if contract is being used in any active transactions
        // Add business logic here when other modules are implemented
        
        return $this->contractRepository->delete($id);
    }

    public function search(string $query)
    {
        return $this->contractRepository->searchContracts($query);
    }

    public function getActiveContracts()
    {
        return $this->contractRepository->getActiveContracts();
    }

    public function getExpiredContracts()
    {
        return $this->contractRepository->getExpiredContracts();
    }

    public function getExpiringSoonContracts(int $days = 30)
    {
        return $this->contractRepository->getExpiringSoonContracts($days);
    }

    public function getContractsBySeller(int $sellerId)
    {
        return $this->contractRepository->getContractsBySeller($sellerId);
    }

    public function getActiveContractsBySeller(int $sellerId)
    {
        return $this->contractRepository->getActiveContractsBySeller($sellerId);
    }

    public function getLatestContractBySeller(int $sellerId)
    {
        return $this->contractRepository->getLatestContractBySeller($sellerId);
    }

    public function getContractsByTeaGrade(string $teaGrade)
    {
        return $this->contractRepository->getContractsByTeaGrade($teaGrade);
    }

    public function getStatistics()
    {
        return $this->contractRepository->getContractStatistics();
    }

    public function updateStatus(int $id, string $status)
    {
        $validStatuses = [
            Contract::STATUS_DRAFT,
            Contract::STATUS_ACTIVE,
            Contract::STATUS_EXPIRED,
            Contract::STATUS_CANCELLED
        ];

        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid contract status');
        }

        return $this->contractRepository->updateContractStatus($id, $status);
    }

    public function activateContract(int $id)
    {
        return $this->updateStatus($id, Contract::STATUS_ACTIVE);
    }

    public function cancelContract(int $id)
    {
        return $this->updateStatus($id, Contract::STATUS_CANCELLED);
    }

    public function expireContract(int $id)
    {
        return $this->updateStatus($id, Contract::STATUS_EXPIRED);
    }

    public function expireOldContracts()
    {
        return $this->contractRepository->expireOldContracts();
    }

    public function getExpiryAlerts()
    {
        return $this->contractRepository->getContractsWithExpiryAlerts();
    }

    public function getPriceForSellerAndTeaGrade(int $sellerId, string $teaGrade)
    {
        return $this->contractRepository->getPriceForSellerAndTeaGrade($sellerId, $teaGrade);
    }

    public function getAvailableTeaGradesForSeller(int $sellerId)
    {
        return $this->contractRepository->getAvailableTeaGradesForSeller($sellerId);
    }

    public function uploadContractFile(int $contractId, $file)
    {
        $contract = $this->contractRepository->find($contractId);
        
        if (!$contract) {
            throw new \Exception('Contract not found');
        }

        // Delete old file if exists
        if ($contract->uploaded_file_path && Storage::exists($contract->uploaded_file_path)) {
            Storage::delete($contract->uploaded_file_path);
        }

        // Store new file
        $path = $file->store('contracts', 'public');
        
        // Update contract with file path
        $this->contractRepository->update($contractId, ['uploaded_file_path' => $path]);
        
        return $path;
    }

    public function exportData(array $filters = [])
    {
        $contracts = $this->contractRepository->getWithFilters($filters);
        
        return $contracts->map(function ($contract) {
            return [
                'ID' => $contract->id,
                'Contract Number' => $contract->contract_number,
                'Contract Title' => $contract->contract_title,
                'Seller' => $contract->seller->seller_name,
                'Effective Date' => $contract->effective_date->format('Y-m-d'),
                'Expiry Date' => $contract->expiry_date->format('Y-m-d'),
                'Status' => $contract->status_text,
                'Total Items' => $contract->total_items,
                'Active Items' => $contract->active_items,
                'Days Remaining' => $contract->days_remaining,
                'Created At' => $contract->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function bulkUpdateStatus(array $ids, string $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            try {
                if ($this->updateStatus($id, $status)) {
                    $updated++;
                }
            } catch (\Exception $e) {
                // Log error but continue with other contracts
                \Log::error("Failed to update contract {$id}: " . $e->getMessage());
            }
        }
        return $updated;
    }

    // Contract Items Methods
    public function getContractItems(int $contractId)
    {
        return $this->contractItemRepository->getActiveItemsByContract($contractId);
    }

    public function addContractItem(int $contractId, array $itemData)
    {
        $itemData['contract_id'] = $contractId;
        return $this->contractItemRepository->create($itemData);
    }

    public function updateContractItem(int $itemId, array $itemData)
    {
        return $this->contractItemRepository->update($itemId, $itemData);
    }

    public function deleteContractItem(int $itemId)
    {
        return $this->contractItemRepository->delete($itemId);
    }

    public function updateItemStatus(int $itemId, bool $status)
    {
        return $this->contractItemRepository->updateItemStatus($itemId, $status);
    }

    protected function createContractItems(int $contractId, array $items)
    {
        $validatedItems = [];
        
        foreach ($items as $item) {
            $this->validateContractItemData($item);
            $validatedItems[] = $item;
        }

        return $this->contractItemRepository->bulkCreateItems($contractId, $validatedItems);
    }

    protected function updateContractItems(int $contractId, array $items)
    {
        // Delete existing items
        $this->contractItemRepository->deleteItemsByContract($contractId);
        
        // Create new items
        if (!empty($items)) {
            return $this->createContractItems($contractId, $items);
        }
    }

    protected function validateContractData(array $data, int $excludeId = null)
    {
        $errors = [];

        // Validate dates
        if (!empty($data['effective_date']) && !empty($data['expiry_date'])) {
            $effectiveDate = \Carbon\Carbon::parse($data['effective_date']);
            $expiryDate = \Carbon\Carbon::parse($data['expiry_date']);
            
            if ($expiryDate <= $effectiveDate) {
                $errors['expiry_date'] = ['Expiry date must be after effective date.'];
            }
        }

        // Validate contract number uniqueness
        if (!empty($data['contract_number'])) {
            $query = Contract::where('contract_number', $data['contract_number']);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            if ($query->exists()) {
                $errors['contract_number'] = ['Contract number already exists.'];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function validateContractItemData(array $data)
    {
        $errors = [];

        if (empty($data['tea_grade'])) {
            $errors['tea_grade'] = ['Tea grade is required.'];
        }

        if (empty($data['price_per_kg']) || $data['price_per_kg'] <= 0) {
            $errors['price_per_kg'] = ['Price per kg is required and must be greater than 0.'];
        }

        if (!empty($data['minimum_quantity']) && !empty($data['maximum_quantity'])) {
            if ($data['minimum_quantity'] > $data['maximum_quantity']) {
                $errors['maximum_quantity'] = ['Maximum quantity must be greater than minimum quantity.'];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function getStatusOptions()
    {
        return Contract::getStatusOptions();
    }

    public function getTeaGradeOptions()
    {
        // This should match the tea grades from Seller model
        return [
            'BP' => 'Broken Pekoe',
            'BOP' => 'Broken Orange Pekoe',
            'BOPF' => 'Broken Orange Pekoe Fannings',
            'PD' => 'Pekoe Dust',
            'Dust' => 'Dust',
            'Fannings' => 'Fannings',
            'CTC' => 'Crush, Tear, Curl',
            'Orthodox' => 'Orthodox'
        ];
    }

    public function getCurrencyOptions()
    {
        return [
            'INR' => 'Indian Rupee',
            'USD' => 'US Dollar',
            'EUR' => 'Euro',
            'GBP' => 'British Pound'
        ];
    }

    // Alert and Notification Methods
    public function sendExpiryAlerts()
    {
        $expiringContracts = $this->getExpiringSoonContracts(30);
        
        foreach ($expiringContracts as $contract) {
            // Send notification to relevant users
            // This would integrate with notification system when implemented
            \Log::info("Contract {$contract->contract_number} is expiring on {$contract->expiry_date->format('Y-m-d')}");
        }
        
        return $expiringContracts->count();
    }

    public function getContractPerformanceData(int $sellerId)
    {
        $contracts = $this->getContractsBySeller($sellerId);
        
        return [
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('status', Contract::STATUS_ACTIVE)->count(),
            'expired_contracts' => $contracts->where('status', Contract::STATUS_EXPIRED)->count(),
            'cancelled_contracts' => $contracts->where('status', Contract::STATUS_CANCELLED)->count(),
            'average_contract_duration' => $this->calculateAverageContractDuration($contracts),
            'total_tea_grades' => $contracts->flatMap->contractItems->pluck('tea_grade')->unique()->count(),
        ];
    }

    public function getForSelect()
    {
        return $this->contractRepository->all(['id', 'contract_number', 'contract_title', 'seller_id'])
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'text' => $contract->contract_number . ' - ' . $contract->contract_title,
                    'seller_id' => $contract->seller_id
                ];
            });
    }

    public function calculateAverageContractDuration($contracts)
    {
        if ($contracts->isEmpty()) {
            return 0;
        }

        $totalDays = $contracts->sum(function ($contract) {
            return $contract->effective_date->diffInDays($contract->expiry_date);
        });

        return round($totalDays / $contracts->count());
    }
}