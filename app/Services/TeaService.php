<?php

namespace App\Services;

use App\Repositories\Interfaces\TeaRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\BaseServiceInterface;


class TeaService implements BaseServiceInterface
{
            protected $repository;

    public function __construct(TeaRepositoryInterface $repository)
    {
        // parent::__construct($repository);
                        $this->repository = $repository;

    }

    public function index(array $filters = [])
    {
        // $query = $this->repository->query();
                $query = $this->repository->getModel()->newQuery(); // Change this line


        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('category', 'LIKE', "%{$search}%")
                  ->orWhere('tea_type', 'LIKE', "%{$search}%")
                  ->orWhere('sub_title', 'LIKE', "%{$search}%")
                  ->orWhere('grade', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['tea_type'])) {
            $query->where('tea_type', $filters['tea_type']);
        }

        if (!empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('category')
                    ->orderBy('tea_type')
                    ->orderBy('sub_title')
                    ->paginate($perPage)
                    ->appends($filters);
    }

     public function show(int $id)
    {
        return $this->repository->find($id);
    }

    public function search(string $query)
    {
        return $this->repository->searchTeas($query);
    }

    public function getForSelect()
    {
        return $this->repository->getActiveTeasList();
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Check if tea combination already exists
            if ($this->repository->checkTeaCombinationExists($data)) {
                throw new \Exception('Tea with this combination of Category, Type, Sub Title, and Grade already exists.');
            }

            // Process characteristics if provided
            if (isset($data['characteristics']) && is_string($data['characteristics'])) {
                $data['characteristics'] = array_map('trim', explode(',', $data['characteristics']));
            }

            $tea = $this->repository->create($data);

            DB::commit();

            Log::info('Tea created successfully', ['tea_id' => $tea->id, 'tea_name' => $tea->full_name]);

            return $tea;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating Tea: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $tea = $this->repository->find($id);
            
            if (!$tea) {
                throw new \Exception('Tea not found.');
            }

            // Check if tea combination already exists (excluding current tea)
            if ($this->repository->checkTeaCombinationExists($data, $id)) {
                throw new \Exception('Tea with this combination of Category, Type, Sub Title, and Grade already exists.');
            }

            // Process characteristics if provided
            if (isset($data['characteristics']) && is_string($data['characteristics'])) {
                $data['characteristics'] = array_map('trim', explode(',', $data['characteristics']));
            }

            $updatedTea = $this->repository->update($id, $data);

            DB::commit();

            Log::info('Tea updated successfully', ['tea_id' => $id]);

            return $updatedTea;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating Tea: ' . $e->getMessage(), ['tea_id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $tea = $this->repository->find($id);
            
            if (!$tea) {
                throw new \Exception('Tea not found.');
            }

            $result = $this->repository->delete($id);

            DB::commit();

            Log::info('Tea deleted successfully', ['tea_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting Tea: ' . $e->getMessage(), ['tea_id' => $id]);
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $tea = $this->repository->find($id);
            
            if (!$tea) {
                throw new \Exception('Tea not found.');
            }

            $newStatus = !$tea->status;
            $this->repository->updateStatus($id, $newStatus);

            Log::info('Tea status updated', ['tea_id' => $id, 'new_status' => $newStatus]);

            return $newStatus;
        } catch (\Exception $e) {
            Log::error('Error updating Tea status: ' . $e->getMessage(), ['tea_id' => $id]);
            throw $e;
        }
    }

    public function getStatistics()
    {
        return $this->repository->getTeaStatistics();
    }

    public function getCategoryOptions()
    {
        return [
            'Black Tea' => 'Black Tea',
            'Green Tea' => 'Green Tea',
            'White Tea' => 'White Tea',
            'Oolong Tea' => 'Oolong Tea',
            'Herbal Tea' => 'Herbal Tea',
            'Specialty Tea' => 'Specialty Tea'
        ];
    }

    public function getTeaTypeOptions()
    {
        return [
            'Orthodox' => 'Orthodox',
            'CTC' => 'CTC',
            'Specialty' => 'Specialty',
            'Organic' => 'Organic'
        ];
    }

    public function getGradeOptions()
    {
        return [
            'BP' => 'Broken Pekoe (BP)',
            'BOP' => 'Broken Orange Pekoe (BOP)',
            'BOPF' => 'Broken Orange Pekoe Fannings (BOPF)',
            'PD' => 'Pekoe Dust (PD)',
            'Dust' => 'Dust',
            'FTGFOP' => 'Finest Tippy Golden Flowery Orange Pekoe (FTGFOP)',
            'TGFOP' => 'Tippy Golden Flowery Orange Pekoe (TGFOP)',
            'GFOP' => 'Golden Flowery Orange Pekoe (GFOP)',
            'FOP' => 'Flowery Orange Pekoe (FOP)',
            'OP' => 'Orange Pekoe (OP)',
            'Pekoe' => 'Pekoe',
            'Souchong' => 'Souchong'
        ];
    }

    public function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    public function getActiveTeasList()
    {
        return $this->repository->getActiveTeasList();
    }
}