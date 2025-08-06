<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\TransporterBranch;
use App\Models\LogisticCompany;
use App\Models\BranchServiceRoute;
use App\Services\TransporterBranchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TransporterBranchController extends BaseAdminController
{
    protected $viewPrefix = 'admin.transporter-branches';
    protected $routePrefix = 'admin.transporter-branches';
    protected $title = 'Transporter Branch';

    public function __construct(TransporterBranchService $transporterBranchService)
    {
        $this->service = $transporterBranchService;
    }

    protected function getPermissionPrefix(): string
    {
        return 'transporter_branches';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'city', 'logistic_company_id', 'per_page']);
        $branches = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $branches,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'branches' => $branches,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'cities' => TransporterBranch::getCityOptions(),
            'logisticCompanies' => LogisticCompany::active()->get(['id', 'company_name']),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(Request $request): View
    {
        $logisticCompanyId = $request->get('logistic_company_id');
        $logisticCompany = null;
        
        if ($logisticCompanyId) {
            $logisticCompany = LogisticCompany::findOrFail($logisticCompanyId);
        }

        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'logisticCompany' => $logisticCompany,
            'logisticCompanies' => LogisticCompany::active()->get(['id', 'company_name']),
            'cities' => TransporterBranch::getCityOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateStore($request);

        try {
            $branch = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $branch
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

    public function show($id): View|JsonResponse
    {
        $branch = $this->service->show((int)$id);

        if (!$branch) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Transporter branch not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($branch->load(['logisticCompany', 'serviceRoutes']));
        }

        return view($this->viewPrefix . '.show', [
            'branch' => $branch,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit($id): View
    {
        $branch = $this->service->show((int)$id);

        if (!$branch) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'transporterBranch' => $branch,
            'logisticCompany' => $branch->logisticCompany,
            'title' => 'Edit ' . $this->title,
            'cities' => TransporterBranch::getCityOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function update(Request $request, $id): RedirectResponse|JsonResponse
    {
        $validatedData = $this->validateUpdate($request, (int)$id);

        try {
            $branch = $this->service->update((int)$id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully.',
                    'data' => $branch
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

    public function destroy($id): JsonResponse
    {
        try {
            $this->service->destroy((int)$id);

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

    public function updateStatus($id): JsonResponse
    {
        try {
            $newStatus = $this->service->toggleStatus((int)$id);

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

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'logistic_company_id' => 'required|integer|exists:logistic_companies,id',
            'branch_name' => 'required|string|max:255',
            'city' => 'required|in:Kolkata,Siliguri,Guwahati',
            'branch_address' => 'required|string',
            'branch_contact_person' => 'required|string|max:255',
            'branch_phone' => 'required|string|max:20',
            'branch_email' => 'nullable|email|max:255',
            'services_offered' => 'nullable|string',
            'operational_hours' => 'nullable|array',
            'operational_hours.*.open' => 'nullable|string',
            'operational_hours.*.close' => 'nullable|string',
            'handling_capacity_tons_per_day' => 'nullable|numeric|min:0',
            'is_main_branch' => 'boolean',
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            
            // Service routes
            'service_routes' => 'nullable|array',
            'service_routes.*.route_from' => 'required|string|max:255',
            'service_routes.*.route_to' => 'required|string|max:255',
            'service_routes.*.distance_km' => 'nullable|numeric|min:0',
            'service_routes.*.estimated_time_hours' => 'nullable|numeric|min:0',
            'service_routes.*.rate_per_kg' => 'nullable|numeric|min:0',
            'service_routes.*.minimum_charge' => 'nullable|numeric|min:0',
            'service_routes.*.express_service_available' => 'boolean'
        ]);
    }

    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'branch_name' => 'required|string|max:255',
            'city' => 'required|in:Kolkata,Siliguri,Guwahati',
            'branch_address' => 'required|string',
            'branch_contact_person' => 'required|string|max:255',
            'branch_phone' => 'required|string|max:20',
            'branch_email' => 'nullable|email|max:255',
            'services_offered' => 'nullable|string',
            'operational_hours' => 'nullable|array',
            'operational_hours.*.open' => 'nullable|string',
            'operational_hours.*.close' => 'nullable|string',
            'handling_capacity_tons_per_day' => 'nullable|numeric|min:0',
            'is_main_branch' => 'boolean',
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            
            // Service routes
            'service_routes' => 'nullable|array',
            'service_routes.*.route_from' => 'required|string|max:255',
            'service_routes.*.route_to' => 'required|string|max:255',
            'service_routes.*.distance_km' => 'nullable|numeric|min:0',
            'service_routes.*.estimated_time_hours' => 'nullable|numeric|min:0',
            'service_routes.*.rate_per_kg' => 'nullable|numeric|min:0',
            'service_routes.*.minimum_charge' => 'nullable|numeric|min:0',
            'service_routes.*.express_service_available' => 'boolean'
        ]);
    }

    // Additional methods for service routes
    public function addServiceRoute(Request $request, $id): JsonResponse
    {
        $request->validate([
            'route_from' => 'required|string|max:255',
            'route_to' => 'required|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'estimated_time_hours' => 'nullable|numeric|min:0',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'minimum_charge' => 'nullable|numeric|min:0',
            'express_service_available' => 'boolean'
        ]);

        try {
            $serviceRoute = $this->service->addServiceRoute((int)$id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Service route added successfully.',
                'data' => $serviceRoute
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function removeServiceRoute($branchId, $routeId): JsonResponse
    {
        try {
            $this->service->removeServiceRoute((int)$branchId, (int)$routeId);

            return response()->json([
                'success' => true,
                'message' => 'Service route removed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getByCompany($companyId): JsonResponse
    {
        try {
            $branches = $this->service->getByCompany((int)$companyId);
            
            return response()->json([
                'success' => true,
                'branches' => $branches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getByCity($city): JsonResponse
    {
        try {
            $branches = $this->service->getByCity($city);
            
            return response()->json([
                'success' => true,
                'branches' => $branches
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
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:transporter_branches,id'
        ]);

        try {
            $count = 0;
            
            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, true);
                    $message = "{$count} transporter branches activated successfully";
                    break;
                    
                case 'deactivate':
                    $count = $this->service->bulkUpdateStatus($request->ids, false);
                    $message = "{$count} transporter branches deactivated successfully";
                    break;
                    
                case 'delete':
                    foreach ($request->ids as $id) {
                        $this->service->destroy($id);
                        $count++;
                    }
                    $message = "{$count} transporter branches deleted successfully";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'city', 'logistic_company_id']);
        $data = $this->service->exportData($filters);

        $filename = 'transporter_branches_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}