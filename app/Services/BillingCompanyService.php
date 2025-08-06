<?php

namespace App\Services;

use App\Models\BillingCompany;
use App\Models\BillingCompanyShippingAddress;
use App\Models\PocBillingCompanyAssignment;
use App\Repositories\BillingCompanyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BillingCompanyService
{
    protected $billingCompanyRepository;

    public function __construct(BillingCompanyRepository $billingCompanyRepository)
    {
        $this->billingCompanyRepository = $billingCompanyRepository;
    }

    public function index(array $filters = [])
    {
        return $this->billingCompanyRepository->getWithFilters($filters);
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Create billing company
            $billingCompany = $this->billingCompanyRepository->create([
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'billing_address' => $data['billing_address'],
                'billing_city' => $data['billing_city'],
                'billing_state' => $data['billing_state'],
                'billing_pincode' => $data['billing_pincode'],
                'gstin' => $data['gstin'] ?? null,
                'pan' => $data['pan'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'] ?? true,
                'remarks' => $data['remarks'] ?? null
            ]);

            // Handle shipping addresses (for buyers)
            if (($data['type'] === 'buyer' || $data['type'] === 'both') && isset($data['shipping_addresses'])) {
                $this->handleShippingAddresses($billingCompany, $data['shipping_addresses']);
            }

            // Handle seller assignments
            if (isset($data['seller_ids']) && !empty($data['seller_ids'])) {
                $this->handleSellerAssignments($billingCompany, $data['seller_ids'], $data['primary_seller_id'] ?? null);
            }

            // Handle POC assignments
            if (isset($data['poc_assignments']) && !empty($data['poc_assignments'])) {
                $this->handlePocAssignments($billingCompany, $data['poc_assignments']);
            }

            DB::commit();
            Log::info('Billing company created successfully', ['billing_company_id' => $billingCompany->id]);

            return $billingCompany;
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error creating billing company: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    public function show(int $id)
    {
        return $this->billingCompanyRepository->find($id);
    }

    public function update(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $billingCompany = $this->billingCompanyRepository->find($id);
            if (!$billingCompany) {
                throw new Exception('Billing company not found.');
            }

            // Update billing company
            $billingCompany = $this->billingCompanyRepository->update($id, [
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'billing_address' => $data['billing_address'],
                'billing_city' => $data['billing_city'],
                'billing_state' => $data['billing_state'],
                'billing_pincode' => $data['billing_pincode'],
                'gstin' => $data['gstin'] ?? null,
                'pan' => $data['pan'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'] ?? true,
                'remarks' => $data['remarks'] ?? null
            ]);

            // Handle shipping addresses
            if (($data['type'] === 'buyer' || $data['type'] === 'both') && isset($data['shipping_addresses'])) {
                // Delete existing shipping addresses and recreate
                $billingCompany->shippingAddresses()->delete();
                $this->handleShippingAddresses($billingCompany, $data['shipping_addresses']);
            }

            // Handle seller assignments
            if (isset($data['seller_ids'])) {
                $billingCompany->sellers()->detach();
                if (!empty($data['seller_ids'])) {
                    $this->handleSellerAssignments($billingCompany, $data['seller_ids'], $data['primary_seller_id'] ?? null);
                }
            }

            // Handle POC assignments
            if (isset($data['poc_assignments'])) {
                $billingCompany->pocAssignments()->delete();
                if (!empty($data['poc_assignments'])) {
                    $this->handlePocAssignments($billingCompany, $data['poc_assignments']);
                }
            }

            DB::commit();
            Log::info('Billing company updated successfully', ['billing_company_id' => $id]);

            return $billingCompany;
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error updating billing company: ' . $e->getMessage(), ['billing_company_id' => $id, 'data' => $data]);
            throw $e;
        }
    }

    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            $billingCompany = $this->billingCompanyRepository->find($id);
            if (!$billingCompany) {
                throw new Exception('Billing company not found.');
            }

            $result = $this->billingCompanyRepository->delete($id);

            DB::commit();
            Log::info('Billing company deleted successfully', ['billing_company_id' => $id]);

            return $result;
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error deleting billing company: ' . $e->getMessage(), ['billing_company_id' => $id]);
            throw $e;
        }
    }

    public function toggleStatus(int $id)
    {
        try {
            $billingCompany = $this->billingCompanyRepository->find($id);
            if (!$billingCompany) {
                throw new Exception('Billing company not found.');
            }

            $newStatus = !$billingCompany->status;
            $this->billingCompanyRepository->updateStatus($id, $newStatus);

            Log::info('Billing company status updated', ['billing_company_id' => $id, 'new_status' => $newStatus]);

            return $newStatus;
        } catch (Exception $e) {
            Log::error('Error updating billing company status: ' . $e->getMessage(), ['billing_company_id' => $id]);
            throw $e;
        }
    }

    public function addShippingAddress(int $billingCompanyId, array $data)
    {
        try {
            $billingCompany = $this->billingCompanyRepository->find($billingCompanyId);
            if (!$billingCompany) {
                throw new Exception('Billing company not found.');
            }

            if (!$billingCompany->canHaveShippingAddresses()) {
                throw new Exception('This billing company type cannot have shipping addresses.');
            }

            // If this is set as default, remove default from others
            if ($data['is_default'] ?? false) {
                $billingCompany->shippingAddresses()->update(['is_default' => false]);
            }

            $shippingAddress = BillingCompanyShippingAddress::create([
                'billing_company_id' => $billingCompanyId,
                'address_label' => $data['address_label'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'],
                'shipping_state' => $data['shipping_state'],
                'shipping_pincode' => $data['shipping_pincode'],
                'contact_person' => $data['contact_person'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'is_default' => $data['is_default'] ?? false,
                'status' => $data['status'] ?? true
            ]);

            Log::info('Shipping address added successfully', ['billing_company_id' => $billingCompanyId, 'address_id' => $shippingAddress->id]);

            return $shippingAddress;
        } catch (Exception $e) {
            Log::error('Error adding shipping address: ' . $e->getMessage(), ['billing_company_id' => $billingCompanyId, 'data' => $data]);
            throw $e;
        }
    }

    public function getByType(string $type)
    {
        return $this->billingCompanyRepository->getByType($type);
    }

    public function getStatistics()
    {
        return $this->billingCompanyRepository->getStatistics();
    }

    public function getTypeOptions()
    {
        return BillingCompany::getTypeOptions();
    }

    public function getStatusOptions()
    {
        return BillingCompany::getStatusOptions();
    }

    public function bulkUpdateStatus(array $ids, bool $status)
    {
        $updated = 0;
        foreach ($ids as $id) {
            try {
                $this->billingCompanyRepository->updateStatus($id, $status);
                $updated++;
            } catch (Exception $e) {
                Log::error('Error in bulk status update for billing company: ' . $e->getMessage(), ['billing_company_id' => $id]);
            }
        }
        return $updated;
    }

    public function exportData(array $filters = [])
    {
        $billingCompanies = $this->billingCompanyRepository->getWithFilters($filters);
        
        return $billingCompanies->map(function ($billingCompany) {
            return [
                'ID' => $billingCompany->id,
                'Company Name' => $billingCompany->company_name,
                'Contact Person' => $billingCompany->contact_person,
                'Email' => $billingCompany->email,
                'Phone' => $billingCompany->phone,
                'Type' => $billingCompany->type_text,
                'GSTIN' => $billingCompany->gstin,
                'PAN' => $billingCompany->pan,
                'Billing Address' => $billingCompany->full_address,
                'Shipping Addresses' => $billingCompany->getShippingAddressesCount(),
                'Status' => $billingCompany->status_text,
                'Created At' => $billingCompany->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    // Private helper methods
    private function handleShippingAddresses(BillingCompany $billingCompany, array $addresses)
    {
        $hasDefault = false;
        
        foreach ($addresses as $addressData) {
            // Ensure only one default address
            if ($addressData['is_default'] ?? false) {
                if ($hasDefault) {
                    $addressData['is_default'] = false;
                } else {
                    $hasDefault = true;
                }
            }

            BillingCompanyShippingAddress::create([
                'billing_company_id' => $billingCompany->id,
                'address_label' => $addressData['address_label'],
                'shipping_address' => $addressData['shipping_address'],
                'shipping_city' => $addressData['shipping_city'],
                'shipping_state' => $addressData['shipping_state'],
                'shipping_pincode' => $addressData['shipping_pincode'],
                'contact_person' => $addressData['contact_person'] ?? null,
                'contact_phone' => $addressData['contact_phone'] ?? null,
                'is_default' => $addressData['is_default'] ?? false,
                'status' => $addressData['status'] ?? true
            ]);
        }

        // If no default address was set, make the first one default
        if (!$hasDefault && count($addresses) > 0) {
            $billingCompany->shippingAddresses()->first()->update(['is_default' => true]);
        }
    }

    private function handleSellerAssignments(BillingCompany $billingCompany, array $sellerIds, $primarySellerId = null)
    {
        foreach ($sellerIds as $sellerId) {
            $isPrimary = ($sellerId == $primarySellerId);
            
            $billingCompany->sellers()->attach($sellerId, [
                'is_primary' => $isPrimary,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function handlePocAssignments(BillingCompany $billingCompany, array $assignments)
    {
        foreach ($assignments as $assignment) {
            // Check if POC is already assigned to another seller
            $existingAssignment = PocBillingCompanyAssignment::where('poc_id', $assignment['poc_id'])
                ->where('seller_id', '!=', $assignment['seller_id'])
                ->first();

            if ($existingAssignment) {
                throw new Exception("POC is already assigned to another seller: {$existingAssignment->seller->seller_name}");
            }

            PocBillingCompanyAssignment::create([
                'poc_id' => $assignment['poc_id'],
                'billing_company_id' => $billingCompany->id,
                'seller_id' => $assignment['seller_id'],
                'is_primary' => $assignment['is_primary'] ?? false
            ]);
        }
    }
}