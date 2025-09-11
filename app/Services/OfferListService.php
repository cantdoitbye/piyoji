<?php

namespace App\Services;

use App\Repositories\Interfaces\OfferListRepositoryInterface;
use App\Repositories\Interfaces\GardenRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class OfferListService
{
    public function __construct(
        protected OfferListRepositoryInterface $repository,
        protected GardenRepositoryInterface $gardenRepository,
        protected SellerRepositoryInterface $sellerRepository,
protected SampleService $sampleService
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
        $samplesCreated = 0;

        Log::info('Starting bulk import', ['total_rows' => count($importData)]);

        foreach ($importData as $index => $data) {
            try {
                Log::info("Processing row " . ($index + 1), ['data' => $data]);

                // Skip empty rows
                if (empty($data['garden_name']) && empty($data['date'])) {
                    Log::info("Skipping empty row " . ($index + 1));
                    continue;
                }

                // Try to find garden by name and map garden_id
                if (!empty($data['garden_name'])) {
                    $garden = $this->gardenRepository->getModel()
                        ->where('garden_name', 'LIKE', "%{$data['garden_name']}%")
                        ->first();
                    
                    if ($garden) {
                        $data['garden_id'] = $garden->id;
                        Log::info("Found garden", ['garden_id' => $garden->id, 'garden_name' => $garden->garden_name]);
                    } else {
                        Log::warning("Garden not found", ['garden_name' => $data['garden_name']]);
                    }
                }

                // Check if offer already exists (by garden_name, grade, date)
                $existingOffer = $this->repository->getModel()
                    ->where('garden_name', $data['garden_name'])
                    ->where('grade', $data['grade'])
                    ->where('date', $data['date'])
                    ->first();

                $offerList = null; // Initialize the variable

                if ($existingOffer) {
                    // Update existing offer
                    $offerList = $this->repository->update($existingOffer->id, $data);
                    $updated++;
                    Log::info("Updated existing offer", ['offer_id' => $offerList->id]);
                } else {
                    // Create new offer
                    $offerList = $this->repository->create($data);
                    $created++;
                    Log::info("Created new offer", ['offer_id' => $offerList->id]);
                }

                // Log offer list data before sample creation
                Log::info("Offer list data for sample creation", [
                    'offer_id' => $offerList->id,
                    'for' => $offerList->for,
                    'garden_name' => $offerList->garden_name,
                    'grade' => $offerList->grade,
                    'inv_pretx' => $offerList->inv_pretx,
                    'inv_no' => $offerList->inv_no,
                    'pkgs' => $offerList->pkgs,
                    'net1' => $offerList->net1,
                    'ttl_kgs' => $offerList->ttl_kgs,
                    'd_o_packing' => $offerList->d_o_packing
                ]);

                // Auto-create sample if conditions are met and offerList is created/updated
                if ($offerList) {
                    Log::info("Attempting to create sample from offer list", ['offer_id' => $offerList->id]);
                    
                    if ($this->autoCreateSampleFromOfferList($offerList)) {
                        $samplesCreated++;
                        Log::info("Sample created successfully", ['offer_id' => $offerList->id]);
                    } else {
                        Log::warning("Sample creation failed or skipped", ['offer_id' => $offerList->id]);
                    }
                } else {
                    Log::error("OfferList is null, cannot create sample", ['row' => $index + 1]);
                }

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                Log::error("Error processing row " . ($index + 1), [
                    'error' => $e->getMessage(),
                    'data' => $data
                ]);
            }
        }

        DB::commit();

        Log::info('Bulk import completed', [
            'created' => $created,
            'updated' => $updated,
            'samples_created' => $samplesCreated,
            'errors_count' => count($errors)
        ]);

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
            'samples_created' => $samplesCreated
        ];

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error in bulk import: ' . $e->getMessage(), [
            'exception' => $e
        ]);
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

  protected function autoCreateSampleFromOfferList($offerList): bool
{
    try {
        Log::info("Starting autoCreateSampleFromOfferList", [
            'offer_id' => $offerList->id,
            'for_value' => $offerList->for,
            'garden_name' => $offerList->garden_name
        ]);

        // Check condition: 'for' column should be 'GTPP'
        if (strtoupper($offerList->for) !== 'GTPP') {
            Log::info("Skipping sample creation - FOR is not GTPP", [
                'offer_id' => $offerList->id,
                'for_value' => $offerList->for
            ]);
            return false;
        }

        Log::info("FOR condition met (GTPP), proceeding with sample creation", [
            'offer_id' => $offerList->id
        ]);

        // Find seller by garden name
        $seller = $this->findSellerByGardenName($offerList->garden_name);
        
        if (!$seller) {
            Log::warning('No seller found for garden', [
                'offer_id' => $offerList->id,
                'garden_name' => $offerList->garden_name
            ]);
            return false;
        }

        Log::info('Seller found for garden', [
            'offer_id' => $offerList->id,
            'seller_id' => $seller->id,
            'seller_name' => $seller->seller_name,
            'garden_name' => $offerList->garden_name
        ]);

        // Check if sample already exists
        $existingSample = \App\Models\Sample::where('garden_name', $offerList->garden_name)
            ->where('grade', $offerList->grade)
            ->where('invoice_prefix', $offerList->inv_pretx)
            ->where('inv_no', $offerList->inv_no)
            ->where('arrival_date', $offerList->d_o_packing)
            ->first();

        if ($existingSample) {
            Log::info('Sample already exists for this offer list', [
                'offer_id' => $offerList->id,
                'existing_sample_id' => $existingSample->id,
                'sample_name' => $existingSample->sample_name
            ]);
            return false;
        }

        Log::info('No existing sample found, creating new sample');

        // Prepare sample data
        $sampleData = [
            'sample_name' => $this->generateSampleName($offerList),
            'seller_id' => $seller->id,
            'garden_name' => $offerList->garden_name,
            'grade' => $offerList->grade,
            'invoice_prefix' => $offerList->inv_pretx,
            'inv_no' => $offerList->inv_no,
            'number_of_samples' => (int) $offerList->pkgs,
            'weight_per_sample' => (float) $offerList->net1,
            'sample_weight' => (float) $offerList->ttl_kgs,
            'arrival_date' => $offerList->d_o_packing,
            'status' => \App\Models\Sample::STATUS_RECEIVED,
            'evaluation_status' => \App\Models\Sample::EVALUATION_PENDING,
            'received_by' => Auth::id() ?? 1,
            'created_by' => Auth::id() ?? 1,
            'updated_by' => Auth::id() ?? 1,
            'source_type' => \App\Models\Sample::SOURCE_OFFER_LIST,
            'source_id' => $offerList->id
        ];

        Log::info('Sample data prepared', [
            'offer_id' => $offerList->id,
            'sample_data' => $sampleData
        ]);

        $sample = $this->sampleService->createSample($sampleData);
        
        Log::info('Sample created successfully', [
            'offer_id' => $offerList->id,
            'sample_id' => $sample->id,
            'sample_name' => $sample->sample_name
        ]);

        return true;

    } catch (\Exception $e) {
        Log::error('Error auto-creating sample from offer list', [
            'offer_id' => $offerList->id,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}

// protected function findSellerByGardenName(string $gardenName)
// {
//     // Find garden first
//     $garden = $this->gardenRepository->getModel()
//         ->where('garden_name', 'LIKE', "%{$gardenName}%")
//         ->first();

//     if ($garden) {
//         $seller = $this->sellerRepository->getModel()
//             ->whereJsonContains('garden_ids', $garden->id)
//             ->where('status', 1)
//             ->first();
        
//         if ($seller) return $seller;
//     }

//     // Fallback: match by tea estate name
//     return $this->sellerRepository->getModel()
//         ->where('tea_estate_name', 'LIKE', "%{$gardenName}%")
//         ->where('status', 'active')
//         ->first();
// }
protected function findSellerByGardenName(string $gardenName)
{
    Log::info('Starting findSellerByGardenName', ['garden_name' => $gardenName]);

    // First try to find garden by name
    $garden = $this->gardenRepository->getModel()
        ->where('garden_name', 'LIKE', "%{$gardenName}%")
        ->first();

    if ($garden) {
        Log::info('Garden found', [
            'garden_id' => $garden->id,
            'garden_name' => $garden->garden_name,
            'poc_ids' => $garden->poc_ids,
            'search_term' => $gardenName
        ]);

        // Check if garden has poc_ids
        if ($garden->poc_ids && is_array($garden->poc_ids) && count($garden->poc_ids) > 0) {
            Log::info('Garden has POC IDs, checking for sellers', [
                'garden_id' => $garden->id,
                'poc_ids' => $garden->poc_ids
            ]);

            // Find seller from the POC IDs associated with this garden
            $seller = $this->sellerRepository->getModel()
                ->whereIn('id', $garden->poc_ids)
                ->where('status', 1)
                ->first();
            
            if ($seller) {
                Log::info('Seller found from garden POC IDs', [
                    'seller_id' => $seller->id,
                    'seller_name' => $seller->seller_name,
                    'garden_id' => $garden->id,
                    'garden_name' => $garden->garden_name,
                    'poc_ids' => $garden->poc_ids
                ]);
                return $seller;
            } else {
                Log::warning('No active seller found in garden POC IDs', [
                    'garden_id' => $garden->id,
                    'garden_name' => $garden->garden_name,
                    'poc_ids' => $garden->poc_ids
                ]);
            }
        } else {
            Log::warning('Garden has no POC IDs', [
                'garden_id' => $garden->id,
                'garden_name' => $garden->garden_name,
                'poc_ids' => $garden->poc_ids
            ]);
        }
    } else {
        Log::warning('Garden not found', ['search_term' => $gardenName]);
    }

    // Fallback: Try to find seller by tea estate name matching garden name
    Log::info('Trying fallback: search by tea estate name', ['search_term' => $gardenName]);
    
    $seller = $this->sellerRepository->getModel()
        ->whereIn('garden_ids', [$garden->id])
        ->where('status', 1)
        ->first();

    if ($seller) {
        Log::info('Seller found by tea estate name fallback', [
            'seller_id' => $seller->id,
            'seller_name' => $seller->seller_name,
            'tea_estate_name' => $seller->tea_estate_name,
            'search_term' => $gardenName
        ]);
    } else {
        Log::warning('No seller found by any method', ['search_term' => $gardenName]);
    }

    return $seller;
}

protected function generateSampleName($offerList): string
{
    $parts = [];
    
    if ($offerList->garden_name) $parts[] = $offerList->garden_name;
    if ($offerList->grade) $parts[] = $offerList->grade;
    if ($offerList->inv_pretx && $offerList->inv_no) {
        $parts[] = $offerList->inv_pretx . $offerList->inv_no;
    }
    if ($offerList->date) $parts[] = $offerList->date->format('dmY');

    return implode('-', $parts);
}
}