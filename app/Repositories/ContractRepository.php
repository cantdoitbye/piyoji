<?php

namespace App\Repositories;

use App\Models\Contract;
use App\Models\ContractItem;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractRepository extends BaseRepository implements ContractRepositoryInterface
{
    public function __construct(Contract $model)
    {
        parent::__construct($model);
    }

    public function getActiveContracts()
    {
        return $this->model->active()
            ->with(['seller:id,seller_name', 'contractItems'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function getExpiredContracts()
    {
        return $this->model->expired()
            ->with(['seller:id,seller_name'])
            ->orderBy('expiry_date', 'desc')
            ->get();
    }

    public function getExpiringSoonContracts(int $days = 30)
    {
        return $this->model->expiringSoon($days)
            ->with(['seller:id,seller_name'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function getContractsBySeller(int $sellerId)
    {
        return $this->model->bySeller($sellerId)
            ->with(['contractItems'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getActiveContractsBySeller(int $sellerId)
    {
        return $this->model->active()
            ->bySeller($sellerId)
            ->with(['contractItems'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function getLatestContractBySeller(int $sellerId)
    {
        return $this->model->bySeller($sellerId)
            ->where('status', Contract::STATUS_ACTIVE)
            ->with(['contractItems'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getContractsByTeaGrade(string $teaGrade)
    {
        return $this->model->byTeaGrade($teaGrade)
            ->active()
            ->with(['seller:id,seller_name', 'contractItems'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function searchContracts(string $query)
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('contract_number', 'LIKE', "%{$query}%")
              ->orWhere('contract_title', 'LIKE', "%{$query}%")
              ->orWhereHas('seller', function($sellerQuery) use ($query) {
                  $sellerQuery->where('seller_name', 'LIKE', "%{$query}%");
              });
        })->with(['seller:id,seller_name'])->get();
    }

    public function getContractStatistics()
    {
        return [
            'total' => $this->model->count(),
            'active' => $this->model->where('status', Contract::STATUS_ACTIVE)->count(),
            'draft' => $this->model->where('status', Contract::STATUS_DRAFT)->count(),
            'expired' => $this->model->where('status', Contract::STATUS_EXPIRED)->count(),
            'cancelled' => $this->model->where('status', Contract::STATUS_CANCELLED)->count(),
            'expiring_soon' => $this->model->expiringSoon(30)->count(),
            'recent' => $this->model->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function getContractWithItems(int $id)
    {
        return $this->model->with([
            'seller:id,seller_name,contact_person,email,phone',
            'contractItems',
            'createdBy:id,name',
            'updatedBy:id,name'
        ])->find($id);
    }

    public function getContractsWithExpiryAlerts()
    {
        return $this->model->expiringSoon(30)
            ->with(['seller:id,seller_name,contact_person,email'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    public function updateContractStatus(int $id, string $status)
    {
        return $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function expireOldContracts()
    {
        return $this->model->where('expiry_date', '<', now())
            ->where('status', '!=', Contract::STATUS_EXPIRED)
            ->update(['status' => Contract::STATUS_EXPIRED]);
    }

    public function getWithFilters(array $filters = [])
    {
        $query = $this->model->with(['seller:id,seller_name']);

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('contract_number', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('contract_title', 'LIKE', "%{$filters['search']}%")
                  ->orWhereHas('seller', function($sellerQuery) use ($filters) {
                      $sellerQuery->where('seller_name', 'LIKE', "%{$filters['search']}%");
                  });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Seller filter
        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        // Tea grade filter
        if (!empty($filters['tea_grade'])) {
            $query->byTeaGrade($filters['tea_grade']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('effective_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('expiry_date', '<=', $filters['date_to']);
        }

        // Expiring soon filter
        if (!empty($filters['expiring_soon'])) {
            $query->expiringSoon(30);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('created_at', 'desc')
                     ->paginate($perPage);
    }

    public function getPriceForSellerAndTeaGrade(int $sellerId, string $teaGrade)
    {
        $contract = $this->model->active()
            ->bySeller($sellerId)
            ->whereHas('contractItems', function($q) use ($teaGrade) {
                $q->where('tea_grade', $teaGrade)->where('is_active', true);
            })
            ->with(['contractItems' => function($q) use ($teaGrade) {
                $q->where('tea_grade', $teaGrade)->where('is_active', true);
            }])
            ->orderBy('created_at', 'desc')
            ->first();

        return $contract && $contract->contractItems->isNotEmpty() 
            ? $contract->contractItems->first()->price_per_kg 
            : null;
    }

    public function getAvailableTeaGradesForSeller(int $sellerId)
    {
        return $this->model->active()
            ->bySeller($sellerId)
            ->with(['contractItems' => function($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->pluck('contractItems')
            ->flatten()
            ->pluck('tea_grade')
            ->unique()
            ->values()
            ->toArray();
    }
}

class ContractItemRepository extends BaseRepository implements ContractItemRepositoryInterface
{
    public function __construct(ContractItem $model)
    {
        parent::__construct($model);
    }

    public function getActiveItemsByContract(int $contractId)
    {
        return $this->model->active()
            ->where('contract_id', $contractId)
            ->orderBy('tea_grade')
            ->get();
    }

    public function getItemsByTeaGrade(string $teaGrade)
    {
        return $this->model->byTeaGrade($teaGrade)
            ->active()
            ->with(['contract.seller:id,seller_name'])
            ->get();
    }

    public function getItemsByContractAndTeaGrade(int $contractId, string $teaGrade)
    {
        return $this->model->where('contract_id', $contractId)
            ->byTeaGrade($teaGrade)
            ->active()
            ->first();
    }

    public function updateItemStatus(int $id, bool $status)
    {
        return $this->model->where('id', $id)->update(['is_active' => $status]);
    }

    public function bulkCreateItems(int $contractId, array $items)
    {
        $data = [];
        foreach ($items as $item) {
            $data[] = array_merge($item, [
                'contract_id' => $contractId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $this->model->insert($data);
    }

    public function deleteItemsByContract(int $contractId)
    {
        return $this->model->where('contract_id', $contractId)->delete();
    }

    public function getTeaGradesByContract(int $contractId)
    {
        return $this->model->where('contract_id', $contractId)
            ->active()
            ->pluck('tea_grade')
            ->toArray();
    }
}