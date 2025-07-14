<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\LogisticCompanyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class LogisticCompanyController extends BaseAdminController
{
    protected $viewPrefix = 'admin.logistics';
    protected $routePrefix = 'admin.logistics';
    protected $title = 'Logistic Company';

    public function __construct(LogisticCompanyService $logisticService)
    {
        $this->service = $logisticService;
        // Comment out parent constructor for now to avoid middleware issues
        // parent::__construct();
    }

    protected function getPermissionPrefix(): string
    {
        return 'logistics';
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
        $filters = $request->only(['search', 'status', 'pricing_type', 'state', 'region', 'route', 'per_page']);
        $companies = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $companies,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'companies' => $companies,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'pricingTypeOptions' => $this->service->getPricingTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'routeOptions' => $this->service->getRouteOptions(),
            'regionOptions' => $this->service->getRegionOptions()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'pricingTypeOptions' => $this->service->getPricingTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'routeOptions' => $this->service->getRouteOptions(),
            'regionOptions' => $this->service->getRegionOptions()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateStore($request);

        try {
            // Validate pricing data
            $this->service->validatePricingData($validatedData);
            
            $company = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully',
                    'data' => $company
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
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

    public function show(int $id): View|JsonResponse
    {
        $company = $this->service->show($id);

        if (!$company) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($company);
        }

        return view($this->viewPrefix . '.show', [
            'company' => $company,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $company = $this->service->show($id);

        if (!$company) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'company' => $company,
            'title' => 'Edit ' . $this->title,
            'pricingTypeOptions' => $this->service->getPricingTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'routeOptions' => $this->service->getRouteOptions(),
            'regionOptions' => $this->service->getRegionOptions()
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateUpdate($request, $id);

        try {
            // Validate pricing data
            $this->service->validatePricingData($validatedData);
            
            $company = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully',
                    'data' => $company
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
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
            'status' => 'required|boolean'
        ]);

        try {
            $this->service->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
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
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:logistic_companies,id'
        ]);

        try {
            $count = 0;

            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, true);
                    break;
                case 'deactivate':
                    $count = $this->service->bulkUpdateStatus($request->ids, false);
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
                'message' => "{$count} companies {$request->action}d successfully"
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
        $filters = $request->only(['search', 'status', 'pricing_type', 'state', 'region', 'route']);
        $data = $this->service->exportData($filters);

        $filename = 'logistic_companies_' . date('Y-m-d_H-i-s') . '.csv';
        
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

    public function getByRegion(Request $request): JsonResponse
    {
        $request->validate([
            'region' => 'required|string'
        ]);

        $companies = $this->service->getCompaniesByRegion($request->region);

        return response()->json($companies);
    }

    public function getByRoute(Request $request): JsonResponse
    {
        $request->validate([
            'route' => 'required|string'
        ]);

        $companies = $this->service->getCompaniesByRoute($request->route);

        return response()->json($companies);
    }

    public function getByState(Request $request): JsonResponse
    {
        $request->validate([
            'state' => 'required|string'
        ]);

        $companies = $this->service->getCompaniesByState($request->state);

        return response()->json($companies);
    }

    public function calculateShippingCost(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'weight' => 'nullable|numeric|min:0',
            'distance' => 'nullable|numeric|min:0'
        ]);

        try {
            $cost = $this->service->calculateShippingCost($id, $request->weight, $request->distance);

            return response()->json([
                'success' => true,
                'cost' => $cost,
                'formatted_cost' => $cost ? "₹" . number_format($cost, 2) : 'Cannot calculate'
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
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('logistic_companies')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'supported_routes' => 'required',
            'supported_regions' => 'required',
            'pricing_type' => 'required|string|in:per_kg,per_km,flat_rate,custom',
            'base_rate' => 'nullable|numeric|min:0',
            'per_kg_rate' => 'nullable|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'pricing_structure' => 'nullable|string',
            'service_description' => 'nullable|string',
            'gstin' => [
                'nullable',
                'string',
                'max:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                Rule::unique('logistic_companies')->ignore($id)
            ],
            'pan' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
                Rule::unique('logistic_companies')->ignore($id)
            ],
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];
    }
}