<?php

namespace App\Services;

use App\Repositories\Interfaces\OfferListRepositoryInterface;
use App\Repositories\Interfaces\GardenRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferListService
{
    public function __construct(
        protected OfferListRepositoryInterface $repository,
        protected GardenRepositoryInterface $gardenRepository
    ) {}

    public function index(array $filters = [])
    {
        $query = $this->repository->getModel()->newQuery()->with('garden');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('garden_name', 'LIKE', "%{$search}%")
                  ->orWhere('grade', 'LIKE', "%{$search}%")
                  ->orWhere('awr_no', 'LIKE', "%{$search}%")
                  ->orWhere('key', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['garden_id'])) {
            $query->where('garden_id', $filters['garden_id']);
        }

        if (!empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        
        return $query->orderBy('date', 'desc')
                    ->paginate($perPage)
                    ->appends($filters);
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Try to find garden by name and map garden_id
            if (!empty($data['garden_name']) && empty($data['garden_id'])) {
                $garden = $this->gardenRepository->getModel()
                    ->where('garden_name', 'LIKE', "%{$data['garden_name']}%")
                    ->first();
                
                if ($garden) {
                    $data['garden_id'] = $garden->id;
                }
            }

            $offerList = $this->repository->create($data);

            DB::commit();

            Log::info('Offer list created successfully', [
                'offer_id' => $offerList->id,
                'garden_name' => $offerList->garden_name
            ]);

            return $offerList;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating offer list: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            // Try to find garden by name and map garden_id
            if (!empty($data['garden_name']) && empty($data['garden_id'])) {
                $garden = $this->gardenRepository->getModel()
                    ->where('garden_name', 'LIKE', "%{$data['garden_name']}%")
                    ->first();
                
                if ($garden) {
                    $data['garden_id'] = $garden->id;
                }
            }

            $offerList = $this->repository->update($id, $data);

            DB::commit();

            return $offerList;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function destroy(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getStatistics()
    {
        return $this->repository->getOfferStatistics();
    }

    public function bulkImportFromExcel(array $importData)
    {
        try {
            DB::beginTransaction();

            $created = 0;
            $errors = [];
            $updated = 0;

            foreach ($importData as $index => $data) {
                try {
                    // Skip empty rows
                    if (empty($data['garden_name']) && empty($data['date'])) {
                        continue;
                    }

                    // Try to find garden by name and map garden_id
                    if (!empty($data['garden_name'])) {
                        $garden = $this->gardenRepository->getModel()
                            ->where('garden_name', 'LIKE', "%{$data['garden_name']}%")
                            ->first();
                        
                        if ($garden) {
                            $data['garden_id'] = $garden->id;
                        }
                    }

                    // Check if offer already exists (by garden_name, grade, date)
                    $existingOffer = $this->repository->getModel()
                        ->where('garden_name', $data['garden_name'])
                        ->where('grade', $data['grade'])
                        ->where('date', $data['date'])
                        ->first();

                    if ($existingOffer) {
                        // Update existing offer
                        $this->repository->update($existingOffer->id, $data);
                        $updated++;
                    } else {
                        // Create new offer
                        $this->repository->create($data);
                        $created++;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in bulk import: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getForSelect()
    {
        return $this->repository->getModel()
            ->select('id', 'garden_name', 'grade', 'date')
            ->orderBy('date', 'desc')
            ->get();
    }
}