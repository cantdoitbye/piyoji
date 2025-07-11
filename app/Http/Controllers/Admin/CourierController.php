<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\CourierService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CourierController extends BaseAdminController
{
    protected $viewPrefix = 'admin.couriers';
    protected $routePrefix = 'admin.couriers';
    protected $title = 'Courier Service';

    public function __construct(CourierService $courierService)
    {
        $this->service = $courierService;
        // Comment out parent constructor for now to avoid middleware issues
        // parent::__construct();
    }

    protected function getPermissionPrefix(): string
    {
        return 'couriers';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'service_area', 'has_api', 'per_page']);
        $couriers = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $couriers,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'couriers' => $couriers,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'serviceAreas' => $this->service->getServiceAreaOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'serviceAreas' => $this->service->getServiceAreaOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function show(int $id): View|JsonResponse
    {
        // For now, just get the courier without relationships until modules are implemented
        $courier = $this->service->show($id);

        if (!$courier) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($courier);
        }

        return view($this->viewPrefix . '.show', [
            'courier' => $courier,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $courier = $this->service->show($id);

        if (!$courier) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'courier' => $courier,
            'title' => 'Edit ' . $this->title,
            'serviceAreas' => $this->service->getServiceAreaOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    protected function validateStore(Request $request): array
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:courier_services,email',
            'phone' => 'required|string|max:20',
            'service_areas' => 'required|array|min:1',
            'service_areas.*' => 'string',
            'api_endpoint' => 'nullable|url',
            'api_token' => 'nullable|string',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'tracking_url_template' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validated = $request->validate($rules);

        // Additional API validation
        if (!empty($validated['api_endpoint'])) {
            $this->service->validateApiCredentials($validated);
        }

        return $validated;
    }

    protected function validateUpdate(Request $request, int $id): array
    {
        $rules = [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('courier_services')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'service_areas' => 'required|array|min:1',
            'service_areas.*' => 'string',
            'api_endpoint' => 'nullable|url',
            'api_token' => 'nullable|string',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'tracking_url_template' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validated = $request->validate($rules);

        // Additional API validation
        if (!empty($validated['api_endpoint'])) {
            $this->service->validateApiCredentials($validated);
        }

        return $validated;
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);

        try {
            $this->service->updateStatus($id, $request->status);
            
            return response()->json([
                'success' => true,
                'message' => 'Courier service status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating courier service status'
            ], 500);
        }
    }

    public function testApi(Request $request, int $id)
    {
        try {
            $result = $this->service->testApiConnection($id);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'API connection successful' : 'API connection failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing API connection: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:courier_services,id'
        ]);

        try {
            $count = 0;
            
            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, true);
                    $message = "{$count} courier services activated successfully";
                    break;
                    
                case 'deactivate':
                    $count = $this->service->bulkUpdateStatus($request->ids, false);
                    $message = "{$count} courier services deactivated successfully";
                    break;
                    
                case 'delete':
                    foreach ($request->ids as $id) {
                        $this->service->destroy($id);
                        $count++;
                    }
                    $message = "{$count} courier services deleted successfully";
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
        $filters = $request->only(['search', 'status', 'service_area', 'has_api']);
        $data = $this->service->exportData($filters);

        $filename = 'courier_services_' . date('Y-m-d_H-i-s') . '.csv';
        
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

    public function getByServiceArea(Request $request)
    {
        $request->validate([
            'area' => 'required|string'
        ]);

        $couriers = $this->service->getCouriersByServiceArea($request->area);
        
        return response()->json($couriers);
    }

    public function generateTrackingUrl(Request $request, int $id)
    {
        $request->validate([
            'tracking_number' => 'required|string'
        ]);

        try {
            $url = $this->service->generateTrackingUrl($id, $request->tracking_number);
            
            return response()->json([
                'success' => true,
                'tracking_url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating tracking URL: ' . $e->getMessage()
            ], 500);
        }
    }
}