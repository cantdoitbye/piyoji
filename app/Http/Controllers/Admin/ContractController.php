<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\ContractService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ContractController extends BaseAdminController
{
    protected $viewPrefix = 'admin.contracts';
    protected $routePrefix = 'admin.contracts';
    protected $title = 'Contract';
    protected $sellerService;

    public function __construct(ContractService $contractService, SellerService $sellerService)
    {
        $this->service = $contractService;
        $this->sellerService = $sellerService;
        // Comment out parent constructor for now to avoid middleware issues
        // parent::__construct();
    }

    protected function getPermissionPrefix(): string
    {
        return 'contracts';
    }

    // Implement required abstract methods from BaseAdminController
    protected function validateStore(Request $request): array
    {
        return $request->validate($this->getValidationRules());
    }

    protected function validateUpdate(Request $request, $id = null): array
    {
        return $request->validate($this->getValidationRules($id));
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'seller_id', 'tea_grade', 'date_from', 'date_to', 'expiring_soon', 'per_page']);
        $contracts = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $contracts,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'contracts' => $contracts,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'statusOptions' => $this->service->getStatusOptions(),
            'teaGradeOptions' => $this->service->getTeaGradeOptions(),
            'sellers' => $this->sellerService->getForSelect()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'statusOptions' => $this->service->getStatusOptions(),
            'teaGradeOptions' => $this->service->getTeaGradeOptions(),
            'currencyOptions' => $this->service->getCurrencyOptions(),
            'sellers' => $this->sellerService->getForSelect()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateStore($request);

        try {
            $contract = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully',
                    'data' => $contract
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.show', $contract->id)
                ->with('success', $this->title . ' created successfully');

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
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id): View|JsonResponse
    {
        $contract = $this->service->show($id);

        if (!$contract) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($contract);
        }

        return view($this->viewPrefix . '.show', [
            'contract' => $contract,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $contract = $this->service->show($id);

        if (!$contract) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'contract' => $contract,
            'title' => 'Edit ' . $this->title,
            'statusOptions' => $this->service->getStatusOptions(),
            'teaGradeOptions' => $this->service->getTeaGradeOptions(),
            'currencyOptions' => $this->service->getCurrencyOptions(),
            'sellers' => $this->sellerService->getForSelect()
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateUpdate($request, $id);

        try {
            $contract = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully',
                    'data' => $contract
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.show', $contract->id)
                ->with('success', $this->title . ' updated successfully');

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
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        try {
            $this->service->destroy($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' deleted successfully'
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' deleted successfully');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:draft,active,expired,cancelled'
        ]);

        try {
            $this->service->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Contract status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function activate(int $id): JsonResponse
    {
        try {
            $this->service->activateContract($id);

            return response()->json([
                'success' => true,
                'message' => 'Contract activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $this->service->cancelContract($id);

            return response()->json([
                'success' => true,
                'message' => 'Contract cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function expire(int $id): JsonResponse
    {
        try {
            $this->service->expireContract($id);

            return response()->json([
                'success' => true,
                'message' => 'Contract expired successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:activate,cancel,expire,delete',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:contracts,id'
        ]);

        try {
            $count = 0;

            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, 'active');
                    break;
                case 'cancel':
                    $count = $this->service->bulkUpdateStatus($request->ids, 'cancelled');
                    break;
                case 'expire':
                    $count = $this->service->bulkUpdateStatus($request->ids, 'expired');
                    break;
                case 'delete':
                    foreach ($request->ids as $id) {
                        $this->service->destroy($id);
                        $count++;
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} contracts {$request->action}d successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'seller_id', 'tea_grade', 'date_from', 'date_to']);
        $data = $this->service->exportData($filters);

        $filename = 'contracts_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function uploadFile(Request $request, int $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:10240' // 10MB max
        ]);

        try {
            $path = $this->service->uploadContractFile($id, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file_path' => $path
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getExpiryAlerts(): JsonResponse
    {
        $alerts = $this->service->getExpiryAlerts();

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'count' => $alerts->count()
        ]);
    }

    public function sendExpiryAlerts(): JsonResponse
    {
        try {
            $count = $this->service->sendExpiryAlerts();

            return response()->json([
                'success' => true,
                'message' => "Expiry alerts sent for {$count} contracts"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getByTeaGrade(Request $request): JsonResponse
    {
        $request->validate([
            'tea_grade' => 'required|string'
        ]);

        $contracts = $this->service->getContractsByTeaGrade($request->tea_grade);

        return response()->json($contracts);
    }

    public function getBySeller(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:sellers,id',
            'active_only' => 'boolean'
        ]);

        if ($request->boolean('active_only')) {
            $contracts = $this->service->getActiveContractsBySeller($request->seller_id);
        } else {
            $contracts = $this->service->getContractsBySeller($request->seller_id);
        }

        return response()->json($contracts);
    }

    public function getPrice(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:sellers,id',
            'tea_grade' => 'required|string'
        ]);

        $price = $this->service->getPriceForSellerAndTeaGrade($request->seller_id, $request->tea_grade);

        return response()->json([
            'success' => true,
            'price' => $price,
            'formatted_price' => $price ? "₹{$price} per kg" : 'Price not available'
        ]);
    }

    public function getTeaGradesBySeller(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:sellers,id'
        ]);

        $teaGrades = $this->service->getAvailableTeaGradesForSeller($request->seller_id);

        return response()->json([
            'success' => true,
            'tea_grades' => $teaGrades
        ]);
    }

    public function getPerformanceData(Request $request): JsonResponse
    {
        $request->validate([
            'seller_id' => 'required|integer|exists:sellers,id'
        ]);

        $performanceData = $this->service->getContractPerformanceData($request->seller_id);

        return response()->json([
            'success' => true,
            'data' => $performanceData
        ]);
    }

    // Contract Items Methods
    public function getItems(int $contractId): JsonResponse
    {
        $items = $this->service->getContractItems($contractId);

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    public function addItem(Request $request, int $contractId): JsonResponse
    {
        $validatedData = $request->validate($this->getItemValidationRules());

        try {
            $item = $this->service->addContractItem($contractId, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Contract item added successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateItem(Request $request, int $contractId, int $itemId): JsonResponse
    {
        $validatedData = $request->validate($this->getItemValidationRules());

        try {
            $item = $this->service->updateContractItem($itemId, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Contract item updated successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function deleteItem(int $contractId, int $itemId): JsonResponse
    {
        try {
            $this->service->deleteContractItem($itemId);

            return response()->json([
                'success' => true,
                'message' => 'Contract item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateItemStatus(Request $request, int $contractId, int $itemId): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $this->service->updateItemStatus($itemId, $request->is_active);

            return response()->json([
                'success' => true,
                'message' => 'Item status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    protected function getValidationRules($id = null): array
    {
        return [
            'seller_id' => 'required|integer|exists:sellers,id',
            'contract_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('contracts')->ignore($id)
            ],
            'contract_title' => 'required|string|max:255',
            'effective_date' => 'required|date',
            'expiry_date' => 'required|date|after:effective_date',
            'status' => 'required|string|in:draft,active,expired,cancelled',
            'terms_and_conditions' => 'nullable|string',
            'remarks' => 'nullable|string',
            'contract_items' => 'nullable|array',
            'contract_items.*.tea_grade' => 'required_with:contract_items|string|max:50',
            'contract_items.*.tea_grade_description' => 'nullable|string|max:255',
            'contract_items.*.price_per_kg' => 'required_with:contract_items|numeric|min:0',
            'contract_items.*.currency' => 'nullable|string|max:3',
            'contract_items.*.minimum_quantity' => 'nullable|numeric|min:0',
            'contract_items.*.maximum_quantity' => 'nullable|numeric|min:0',
            'contract_items.*.quality_parameters' => 'nullable|string',
            'contract_items.*.special_terms' => 'nullable|string',
            'contract_items.*.is_active' => 'boolean'
        ];
    }

    protected function getItemValidationRules(): array
    {
        return [
            'tea_grade' => 'required|string|max:50',
            'tea_grade_description' => 'nullable|string|max:255',
            'price_per_kg' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'minimum_quantity' => 'nullable|numeric|min:0',
            'maximum_quantity' => 'nullable|numeric|min:0|gte:minimum_quantity',
            'quality_parameters' => 'nullable|string',
            'special_terms' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }
}