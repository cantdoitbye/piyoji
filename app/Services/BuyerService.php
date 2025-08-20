<?php

namespace App\Services;

use App\Models\Buyer;
use App\Models\Sample;
use App\Models\SampleTransfer;
use App\Repositories\Interfaces\BuyerRepositoryInterface;
use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    /**
     * Transfer sample to another batch for retesting
     */
    public function transferSampleToBatch(int $sampleId, array $transferData): array
    {
        try {
            DB::beginTransaction();

            $originalSample = Sample::with(['batchGroup', 'seller'])->findOrFail($sampleId);

            // Validate transfer data
            $this->validateTransferData($originalSample, $transferData);

            // Calculate remaining weights and quantities
            $remainingWeight = $originalSample->sample_weight - $transferData['transferred_weight'];
            $remainingQuantity = $originalSample->number_of_samples - $transferData['transferred_quantity'];

            // Create new sample for the transferred portion
            $newSample = $this->createTransferredSample($originalSample, $transferData);

            // Update original sample with remaining portion
            $this->updateOriginalSample($originalSample, $remainingWeight, $remainingQuantity);

            // Create transfer record
            $transfer = SampleTransfer::create([
                'original_sample_id' => $originalSample->id,
                'new_sample_id' => $newSample->id,
                'from_batch_group_id' => $originalSample->batch_group_id,
                'to_batch_group_id' => null, // Will be set when new sample is batched
                'from_batch_id' => $originalSample->batch_id,
                'to_batch_id' => null, // Will be set when new sample is batched
                'transferred_weight' => $transferData['transferred_weight'],
                'transferred_quantity' => $transferData['transferred_quantity'],
                'remaining_weight' => $remainingWeight,
                'remaining_quantity' => $remainingQuantity,
                'transfer_reason' => $transferData['transfer_reason'],
                'transfer_remarks' => $transferData['transfer_remarks'] ?? null,
                'status' => SampleTransfer::STATUS_COMPLETED,
                'transfer_date' => now(),
                'transferred_by' => Auth::id()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Sample successfully transferred for retesting',
                'original_sample' => $originalSample->fresh(),
                'new_sample' => $newSample,
                'transfer' => $transfer
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate transfer data
     */
    private function validateTransferData(Sample $sample, array $data): void
    {
        // Check if sample is batched
        if (!$sample->batch_group_id) {
            throw new \Exception('Sample must be batched before it can be transferred');
        }

        // Check if sample has been evaluated
        if ($sample->evaluation_status !== Sample::EVALUATION_COMPLETED) {
            throw new \Exception('Only evaluated samples can be transferred for retesting');
        }

        // Validate transferred weight
        if ($data['transferred_weight'] <= 0 || $data['transferred_weight'] > $sample->sample_weight) {
            throw new \Exception('Transferred weight must be greater than 0 and not exceed available weight');
        }

        // Validate transferred quantity
        if ($data['transferred_quantity'] <= 0 || $data['transferred_quantity'] > $sample->number_of_samples) {
            throw new \Exception('Transferred quantity must be greater than 0 and not exceed available quantity');
        }

        // Ensure at least some portion remains in original sample
        if ($data['transferred_weight'] == $sample->sample_weight && $data['transferred_quantity'] == $sample->number_of_samples) {
            throw new \Exception('Cannot transfer entire sample. Some portion must remain in the original sample');
        }
    }

    /**
     * Create new sample for transferred portion
     */
    private function createTransferredSample(Sample $originalSample, array $transferData): Sample
    {
        // Generate new sample ID
        $newSampleId = Sample::generateSampleId();

        // Calculate weight per sample for new sample
        $weightPerSample = $transferData['transferred_quantity'] > 0 
            ? $transferData['transferred_weight'] / $transferData['transferred_quantity'] 
            : $transferData['transferred_weight'];

        $newSample = Sample::create([
            'sample_id' => $newSampleId,
            'sample_name' => $originalSample->sample_name . ' (Retesting)',
            'seller_id' => $originalSample->seller_id,
            'batch_id' => null, // Will be assigned when batched
            'batch_group_id' => null, // Will be assigned when batched
            'number_of_samples' => $transferData['transferred_quantity'],
            'weight_per_sample' => $weightPerSample,
            'sample_weight' => $transferData['transferred_weight'],
            'arrival_date' => now()->format('Y-m-d'),
            'received_by' => Auth::id(),
            'status' => Sample::STATUS_RECEIVED,
            'evaluation_status' => Sample::EVALUATION_PENDING,
            'remarks' => 'Transferred from ' . $originalSample->sample_id . ' for ' . $transferData['transfer_reason'],
            'created_by' => Auth::id(),
            'updated_by' => Auth::id()
        ]);

        return $newSample;
    }

    /**
     * Update original sample with remaining portion
     */
    private function updateOriginalSample(Sample $sample, float $remainingWeight, int $remainingQuantity): void
    {
        $weightPerSample = $remainingQuantity > 0 ? $remainingWeight / $remainingQuantity : 0;

        $sample->update([
            'number_of_samples' => $remainingQuantity,
            'weight_per_sample' => $weightPerSample,
            'sample_weight' => $remainingWeight,
            'updated_by' => Auth::id(),
            'remarks' => ($sample->remarks ? $sample->remarks . '. ' : '') . 'Partial transfer completed on ' . now()->format('Y-m-d')
        ]);
    }

    /**
     * Get transfer history for a sample
     */
    public function getSampleTransferHistory(int $sampleId): array
    {
        $sample = Sample::findOrFail($sampleId);

        $transfersFrom = SampleTransfer::with(['newSample', 'toBatchGroup', 'transferredBy'])
            ->where('original_sample_id', $sampleId)
            ->orderBy('transfer_date', 'desc')
            ->get();

        $transfersTo = SampleTransfer::with(['originalSample', 'fromBatchGroup', 'transferredBy'])
            ->where('new_sample_id', $sampleId)
            ->orderBy('transfer_date', 'desc')
            ->get();

        return [
            'sample' => $sample,
            'transfers_from' => $transfersFrom,
            'transfers_to' => $transfersTo,
            'total_transfers' => $transfersFrom->count() + $transfersTo->count()
        ];
    }

    /**
     * Get all sample transfers with filters
     */
    public function getAllTransfers(array $filters = [])
    {
        $query = SampleTransfer::with([
            'originalSample.seller',
            'newSample.seller', 
            'fromBatchGroup',
            'toBatchGroup',
            'transferredBy'
        ])->orderBy('transfer_date', 'desc');

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['transfer_reason'])) {
            $query->where('transfer_reason', $filters['transfer_reason']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('transfer_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('transfer_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('originalSample', function ($q) use ($search) {
                $q->where('sample_id', 'like', '%' . $search . '%')
                  ->orWhere('sample_name', 'like', '%' . $search . '%');
            })->orWhereHas('newSample', function ($q) use ($search) {
                $q->where('sample_id', 'like', '%' . $search . '%')
                  ->orWhere('sample_name', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Cancel a pending transfer
     */
    public function cancelTransfer(int $transferId): bool
    {
        try {
            DB::beginTransaction();

            $transfer = SampleTransfer::findOrFail($transferId);

            if ($transfer->status !== SampleTransfer::STATUS_PENDING) {
                throw new \Exception('Only pending transfers can be cancelled');
            }

            $transfer->update([
                'status' => SampleTransfer::STATUS_CANCELLED,
                'updated_at' => now()
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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