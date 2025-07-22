<?php

namespace App\Services;

use App\Repositories\Interfaces\GardenRepositoryInterface;
use App\Repositories\Interfaces\TeaRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\BaseServiceInterface;


class GardenService implements BaseServiceInterface
{
    protected $repository;
        protected $teaRepository;


    public function __construct(GardenRepositoryInterface $repository, TeaRepositoryInterface $teaRepository)
    {
        // parent::__construct($repository);
        $this->repository = $repository;
                $this->teaRepository = $teaRepository;

    }

    public function index(array $filters = [])
    {
                $query = $this->repository->getModel()->newQuery(); 

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('garden_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person_name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile_no', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%")
                  ->orWhere('state', 'LIKE', "%{$search}%")
                  ->orWhere('speciality', 'LIKE', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (!empty($filters['tea_id'])) {
            $query->whereJsonContains('tea_ids', (int) $filters['tea_id']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('garden_name')
                    ->paginate($perPage)
                    ->appends($filters);
    }

    

    public function search(string $query)
    {
        return $this->repository->searchGardens($query);
    }

    public function getForSelect()
    {
        return $this->repository->getActiveGardensList();
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Ensure tea_ids is an array of integers
            if (isset($data['tea_ids'])) {
                $data['tea_ids'] = array_map('intval', array_filter($data['tea_ids']));
            } else {
                $data['tea_ids'] = [];
            }

            $garden = $this->repository->create($data);

            DB::commit();

            Log::info('Garden created successfully', ['garden_id' => $garden->id, 'garden_name' => $garden->garden_name]);

            return $garden;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating Garden: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $garden = $this->repository->find($id);
            
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            // Ensure tea_ids is an array of integers
            if (isset($data['tea_ids'])) {
                $data['tea_ids'] = array_map('intval', array_filter($data['tea_ids']));
            } else {
                $data['tea_ids'] = [];
            }

            $updatedGarden = $this->repository->update($id, $data);

            DB::commit();

            Log::info('Garden updated successfully', ['garden_id' => $id]);

            return $updatedGarden;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating Garden: ' . $e->getMessage(), ['garden_id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function show(int $id)
    {
        return $this->repository->getGardenWithTeas($id);
    }

    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $garden = $this->repository->find($id);
            
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            $result = $this->repository->delete($id);

            DB::commit();

            Log::info('Garden deleted successfully', ['garden_id' => $id]);

            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting Garden: ' . $e->getMessage(), ['garden_id' => $id]);
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $garden = $this->repository->find($id);
            
            if (!$garden) {
                throw new \Exception('Garden not found.');
            }

            $newStatus = !$garden->status;
            $this->repository->updateStatus($id, $newStatus);

            Log::info('Garden status updated', ['garden_id' => $id, 'new_status' => $newStatus]);

            return $newStatus;
        } catch (\Exception $e) {
            Log::error('Error updating Garden status: ' . $e->getMessage(), ['garden_id' => $id]);
            throw $e;
        }
    }

    public function getStatistics()
    {
        return $this->repository->getGardenStatistics();
    }

    public function getStatusOptions()
    {
        return [
            '1' => 'Active',
            '0' => 'Inactive'
        ];
    }

    public function getStatesOptions()
    {
        return [
            'Assam' => 'Assam',
            'Darjeeling' => 'Darjeeling',
            'Kerala' => 'Kerala',
            'Tamil Nadu' => 'Tamil Nadu',
            'Karnataka' => 'Karnataka',
            'Himachal Pradesh' => 'Himachal Pradesh',
            'Uttarakhand' => 'Uttarakhand',
            'Arunachal Pradesh' => 'Arunachal Pradesh',
            'Meghalaya' => 'Meghalaya',
            'Manipur' => 'Manipur',
            'Mizoram' => 'Mizoram',
            'Nagaland' => 'Nagaland',
            'Tripura' => 'Tripura',
            'Sikkim' => 'Sikkim'
        ];
    }

    public function getActiveGardensList()
    {
        return $this->repository->getActiveGardensList();
    }

    public function getAvailableTeas()
    {
        return $this->teaRepository->getActiveTeasList();
    }
}