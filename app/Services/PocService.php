<?php

namespace App\Services;

use App\Repositories\Interfaces\PocRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\BaseServiceInterface;


class PocService implements BaseServiceInterface
{
        protected $repository;

    public function __construct(PocRepositoryInterface $repository)
    {
        // parent::__construct($repository);
                $this->repository = $repository;

    }

    public function index(array $filters = [])
    {
        // $query = $this->repository->query();
                $query = $this->repository->getModel()->newQuery(); 


        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('poc_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('designation', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        if (!empty($filters['poc_type'])) {
            $query->where('poc_type', $filters['poc_type']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('poc_name')
                    ->paginate($perPage)
                    ->appends($filters);
    }

       public function show(int $id)
    {
        return $this->repository->find($id);
    }

    public function search(string $query)
    {
        return $this->repository->searchPocs($query);
    }

    public function getForSelect()
    {
        return $this->repository->getActivePocsList();
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Check if email already exists
            if ($this->repository->checkEmailExists($data['email'])) {
                throw new \Exception('Email already exists.');
            }

            $poc = $this->repository->create($data);

            DB::commit();

            Log::info('POC created successfully', ['poc_id' => $poc->id, 'poc_name' => $poc->poc_name]);

            return $poc;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating POC: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $poc = $this->repository->find($id);
            
            if (!$poc) {
                throw new \Exception('POC not found.');
            }

            // Check if email already exists (excluding current POC)
            if ($this->repository->checkEmailExists($data['email'], $id)) {
                throw new \Exception('Email already exists.');
            }

            $updatedPoc = $this->repository->update($id, $data);

            DB::commit();

            Log::info('POC updated successfully', ['poc_id' => $id]);

            return $updatedPoc;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating POC: ' . $e->getMessage(), ['poc_id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $poc = $this->repository->find($id);
            
            if (!$poc) {
                throw new \Exception('POC not found.');
            }

            $result = $this->repository->delete($id);

            DB::commit();

            Log::info('POC deleted successfully', ['poc_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting POC: ' . $e->getMessage(), ['poc_id' => $id]);
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $poc = $this->repository->find($id);
            
            if (!$poc) {
                throw new \Exception('POC not found.');
            }

            $newStatus = !$poc->status;
            $this->repository->updateStatus($id, $newStatus);

            Log::info('POC status updated', ['poc_id' => $id, 'new_status' => $newStatus]);

            return $newStatus;
        } catch (\Exception $e) {
            Log::error('Error updating POC status: ' . $e->getMessage(), ['poc_id' => $id]);
            throw $e;
        }
    }

    public function getStatistics()
    {
        return $this->repository->getPocStatistics();
    }

    public function getPocTypeOptions()
    {
        return [
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            'both' => 'Both'
        ];
    }

      public function getTypeOptions()
    {
        return [
            'poc' => 'Poc',
            'tester' => 'Tester'
        ];
    }

    public function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    public function getActivePocsList()
    {
        return $this->repository->getActivePocsList();
    }

    public function getPocsByType(string $type)
    {
        return $this->repository->getPocsByType($type);
    }
}