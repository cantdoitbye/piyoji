<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\BuyerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class BuyerController extends BaseAdminController
{
    protected $viewPrefix = 'admin.buyers';
    protected $routePrefix = 'admin.buyers';
    protected $title = 'Buyer';

    public function __construct(BuyerService $buyerService)
    {
        $this->service = $buyerService;
        // Comment out parent constructor for now to avoid middleware issues
        // parent::__construct();
    }

    protected function getPermissionPrefix(): string
    {
        return 'buyers';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'buyer_type', 'tea_grade', 'state', 'per_page']);
        $buyers = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $buyers,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'buyers' => $buyers,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'buyerTypes' => $this->service->getBuyerTypeOptions(),
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'buyerTypes' => $this->service->getBuyerTypeOptions(),
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function show(int $id): View|JsonResponse
    {
        // For now, just get the buyer without relationships until modules are implemented
        $buyer = $this->service->show($id);

        if (!$buyer) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($buyer);
        }

        return view($this->viewPrefix . '.show', [
            'buyer' => $buyer,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $buyer = $this->service->show($id);

        if (!$buyer) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'buyer' => $buyer,
            'title' => 'Edit ' . $this->title,
            'buyerTypes' => $this->service->getBuyerTypeOptions(),
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_type' => 'required|in:' . implode(',', array_keys($this->service->getBuyerTypeOptions())),
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:buyers,email',
            'phone' => 'required|string|max:20',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_pincode' => 'required|string|max:10',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_pincode' => 'required|string|max:10',
            'preferred_tea_grades' => 'required|array|min:1',
            'preferred_tea_grades.*' => 'string|in:' . implode(',', array_keys($this->service->getTeaGradeOptions())),
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            'same_as_billing' => 'nullable|boolean'
        ]);
    }

    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_type' => 'required|in:' . implode(',', array_keys($this->service->getBuyerTypeOptions())),
            'contact_person' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('buyers')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:100',
            'billing_state' => 'required|string|max:100',
            'billing_pincode' => 'required|string|max:10',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_pincode' => 'required|string|max:10',
            'preferred_tea_grades' => 'required|array|min:1',
            'preferred_tea_grades.*' => 'string|in:' . implode(',', array_keys($this->service->getTeaGradeOptions())),
            'status' => 'boolean',
            'remarks' => 'nullable|string',
            'same_as_billing' => 'nullable|boolean'
        ]);
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
                'message' => 'Buyer status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating buyer status'
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:buyers,id'
        ]);

        try {
            $count = 0;
            
            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, true);
                    $message = "{$count} buyers activated successfully";
                    break;
                    
                case 'deactivate':
                    $count = $this->service->bulkUpdateStatus($request->ids, false);
                    $message = "{$count} buyers deactivated successfully";
                    break;
                    
                case 'delete':
                    foreach ($request->ids as $id) {
                        $this->service->destroy($id);
                        $count++;
                    }
                    $message = "{$count} buyers deleted successfully";
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
        $filters = $request->only(['search', 'status', 'buyer_type', 'tea_grade', 'state']);
        $data = $this->service->exportData($filters);

        $filename = 'buyers_' . date('Y-m-d_H-i-s') . '.csv';
        
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

    public function getByType(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:big,small'
        ]);

        $buyers = $this->service->getBuyersByType($request->type);
        
        return response()->json($buyers);
    }

    public function getByTeaGrade(Request $request)
    {
        $request->validate([
            'grade' => 'required|string'
        ]);

        $buyers = $this->service->getBuyersByTeaGrade($request->grade);
        
        return response()->json($buyers);
    }
}