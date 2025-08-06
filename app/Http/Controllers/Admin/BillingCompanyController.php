<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\BillingCompany;
use App\Models\Seller;
use App\Models\Poc;
use App\Services\BillingCompanyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BillingCompanyController extends BaseAdminController
{
    protected $viewPrefix = 'admin.billing-companies';
    protected $routePrefix = 'admin.billing-companies';
    protected $title = 'Billing Company';

    public function __construct(BillingCompanyService $billingCompanyService)
    {
        $this->service = $billingCompanyService;
    }

    protected function getPermissionPrefix(): string
    {
        return 'billing_companies';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'type', 'state', 'per_page']);
        $billingCompanies = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $billingCompanies,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'billingCompanies' => $billingCompanies,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'typeOptions' => $this->service->getTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'typeOptions' => $this->service->getTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'sellers' => Seller::active()->get(),
            'pocs' => Poc::active()->get()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateStore($request);

        try {
            $billingCompany = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $billingCompany
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' created successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateUpdate($request, $id);

        try {
            $billingCompany = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully.',
                    'data' => $billingCompany
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' updated successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:billing_companies,email',
            'phone' => 'required|string|max:20',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_pincode' => 'required|string|max:10',
            'gstin' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'type' => 'required|in:seller,buyer,both',
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            
            // Shipping addresses (for buyers)
            'shipping_addresses' => 'sometimes|array',
            'shipping_addresses.*.address_label' => 'required|string|max:255',
            'shipping_addresses.*.shipping_address' => 'required|string',
            'shipping_addresses.*.shipping_city' => 'required|string|max:255',
            'shipping_addresses.*.shipping_state' => 'required|string|max:255',
            'shipping_addresses.*.shipping_pincode' => 'required|string|max:10',
            'shipping_addresses.*.contact_person' => 'nullable|string|max:255',
            'shipping_addresses.*.contact_phone' => 'nullable|string|max:20',
            'shipping_addresses.*.is_default' => 'boolean',
            
            // Seller assignments
            'seller_ids' => 'sometimes|array',
            'seller_ids.*' => 'integer|exists:sellers,id',
            'primary_seller_id' => 'sometimes|integer|exists:sellers,id',
            
            // POC assignments
            'poc_assignments' => 'sometimes|array',
            'poc_assignments.*.poc_id' => 'required|integer|exists:pocs,id',
            'poc_assignments.*.seller_id' => 'required|integer|exists:sellers,id',
            'poc_assignments.*.is_primary' => 'boolean'
        ]);
    }

    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:billing_companies,email,' . $id,
            'phone' => 'required|string|max:20',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_pincode' => 'required|string|max:10',
            'gstin' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'type' => 'required|in:seller,buyer,both',
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            
            // Similar validation rules for shipping addresses, sellers, and POCs
            'shipping_addresses' => 'sometimes|array',
            'shipping_addresses.*.address_label' => 'required|string|max:255',
            'shipping_addresses.*.shipping_address' => 'required|string',
            'shipping_addresses.*.shipping_city' => 'required|string|max:255',
            'shipping_addresses.*.shipping_state' => 'required|string|max:255',
            'shipping_addresses.*.shipping_pincode' => 'required|string|max:10',
            'shipping_addresses.*.contact_person' => 'nullable|string|max:255',
            'shipping_addresses.*.contact_phone' => 'nullable|string|max:20',
            'shipping_addresses.*.is_default' => 'boolean',
            
            'seller_ids' => 'sometimes|array',
            'seller_ids.*' => 'integer|exists:sellers,id',
            'primary_seller_id' => 'sometimes|integer|exists:sellers,id',
            
            'poc_assignments' => 'sometimes|array',
            'poc_assignments.*.poc_id' => 'required|integer|exists:pocs,id',
            'poc_assignments.*.seller_id' => 'required|integer|exists:sellers,id',
            'poc_assignments.*.is_primary' => 'boolean'
        ]);
    }
       

    public function show(int $id): View|JsonResponse
    {
        $billingCompany = $this->service->show($id);

        if (!$billingCompany) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Billing company not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($billingCompany->load(['shippingAddresses', 'sellers', 'pocAssignments.poc']));
        }

        return view($this->viewPrefix . '.show', [
            'billingCompany' => $billingCompany,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $billingCompany = $this->service->show($id);

        if (!$billingCompany) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'billingCompany' => $billingCompany,
            'title' => 'Edit ' . $this->title,
            'typeOptions' => $this->service->getTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'sellers' => Seller::active()->get(),
            'pocs' => Poc::active()->get()
        ]);
    }

    // public function update(Request $request, int $id): RedirectResponse|JsonResponse
    // {
    //     $rules = [
    //         'company_name' => 'required|string|max:255',
    //         'contact_person' => 'required|string|max:255',
    //         'email' => 'required|email|unique:billing_companies,email,' . $id,
    //         'phone' => 'required|string|max:20',
    //         'billing_address' => 'required|string',
    //         'billing_city' => 'required|string|max:255',
    //         'billing_state' => 'required|string|max:255',
    //         'billing_pincode' => 'required|string|max:10',
    //         'gstin' => 'nullable|string|size:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
    //         'pan' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
    //         'type' => 'required|in:seller,buyer,both',
    //         'status' => 'boolean',
    //         'remarks' => 'nullable|string',
            
    //         // Similar validation rules for shipping addresses, sellers, and POCs
    //         'shipping_addresses' => 'sometimes|array',
    //         'seller_ids' => 'sometimes|array',
    //         'poc_assignments' => 'sometimes|array'
    //     ];

    //     $validatedData = $request->validate($rules);

    //     try {
    //         $billingCompany = $this->service->update($id, $validatedData);

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => $this->title . ' updated successfully.',
    //                 'data' => $billingCompany
    //             ]);
    //         }

    //         return redirect()
    //             ->route($this->routePrefix . '.index')
    //             ->with('success', $this->title . ' updated successfully.');

    //     } catch (\Exception $e) {
    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $e->getMessage()
    //             ], 422);
    //         }

    //         return redirect()
    //             ->back()
    //             ->withInput()
    //             ->with('error', $e->getMessage());
    //     }
    // }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->destroy($id);

            return response()->json([
                'success' => true,
                'message' => $this->title . ' deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateStatus(int $id): JsonResponse
    {
        try {
            $newStatus = $this->service->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => $this->title . ' status updated successfully.',
                'status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // Additional methods for shipping addresses
    public function addShippingAddress(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'address_label' => 'required|string|max:255',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_pincode' => 'required|string|max:10',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_default' => 'boolean'
        ]);

        try {
            $shippingAddress = $this->service->addShippingAddress($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Shipping address added successfully.',
                'data' => $shippingAddress
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getByType(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:seller,buyer,both'
        ]);

        $billingCompanies = $this->service->getByType($request->type);
        
        return response()->json($billingCompanies);
    }
}