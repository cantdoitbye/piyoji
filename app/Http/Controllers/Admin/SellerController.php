<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Poc;
use App\Services\GardenService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class SellerController extends BaseAdminController
{
    protected $viewPrefix = 'admin.sellers';
    protected $routePrefix = 'admin.sellers';
    protected $title = 'Seller';
protected $gardenService;

    public function __construct(SellerService $sellerService,GardenService $gardenService)
    {
        $this->service = $sellerService;
        $this->gardenService = $gardenService;

        // Comment out parent constructor for now to avoid middleware issues
        // parent::__construct();
    }

    protected function getPermissionPrefix(): string
    {
        return 'sellers';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'tea_grade', 'state', 'per_page']);
        $sellers = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $sellers,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'sellers' => $sellers,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
            $pocs = Poc::active()->forSellers()->orderBy('poc_name')->get();
    $gardens = $this->gardenService->getForSelect(); // Add this line
    $states = $this->getIndianStates();

        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'pocs' => $pocs,
            'gardens' => $gardens,
            'states' => $states
        ]);
    }

    public function show(int $id): View|JsonResponse
    {
        // For now, just get the seller without relationships until modules are implemented
        $seller = $this->service->show($id);

        if (!$seller) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($seller);
        }

        return view($this->viewPrefix . '.show', [
            'seller' => $seller,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $seller = $this->service->show($id);

        if (!$seller) {
            abort(404);
        }
            $pocs = Poc::active()->forBuyers()->orderBy('poc_name')->get(); // Add this line
    $states = $this->getIndianStates();


        return view($this->viewPrefix . '.create', [
            'seller' => $seller,
            'title' => 'Edit ' . $this->title,
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'pocs' => $pocs,
            'gardens' => $this->gardenService->getForSelect(),
            'states' => $states

        ]);
    }

    protected function validateStore(Request $request): array
    {
        return $request->validate([
            'seller_name' => 'required|string|max:255',
            'tea_estate_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:sellers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'gstin' => 'required|string|max:15|unique:sellers,gstin',
            'pan' => 'required|string|max:10|unique:sellers,pan',
            'tea_grades' => 'required|array|min:1',
            'tea_grades.*' => 'string|in:' . implode(',', array_keys($this->service->getTeaGradeOptions())),
            'status' => 'boolean',
                        'type' => 'required|in:group,individual',
             'poc_ids' => 'nullable|array',
        'poc_ids.*' => 'exists:pocs,id',
            'remarks' => 'nullable|string'
        ]);
    }

    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'seller_name' => 'required|string|max:255',
            'tea_estate_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('sellers')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'gstin' => [
                'required',
                'string',
                'max:15',
                Rule::unique('sellers')->ignore($id)
            ],
            'pan' => [
                'required',
                'string',
                'max:10',
                Rule::unique('sellers')->ignore($id)
            ],
            'tea_grades' => 'required|array|min:1',
            'tea_grades.*' => 'string|in:' . implode(',', array_keys($this->service->getTeaGradeOptions())),
            'garden_ids' => 'nullable|array',
            'garden_ids.*' => 'integer|exists:gardens,id',
            'type' => 'required|in:group,individual',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
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
                'message' => 'Seller status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating seller status'
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:sellers,id'
        ]);

        try {
            $count = 0;
            
            switch ($request->action) {
                case 'activate':
                    $count = $this->service->bulkUpdateStatus($request->ids, true);
                    $message = "{$count} sellers activated successfully";
                    break;
                    
                case 'deactivate':
                    $count = $this->service->bulkUpdateStatus($request->ids, false);
                    $message = "{$count} sellers deactivated successfully";
                    break;
                    
                case 'delete':
                    foreach ($request->ids as $id) {
                        $this->service->destroy($id);
                        $count++;
                    }
                    $message = "{$count} sellers deleted successfully";
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
        $filters = $request->only(['search', 'status', 'tea_grade', 'state']);
        $data = $this->service->exportData($filters);

        $filename = 'sellers_' . date('Y-m-d_H-i-s') . '.csv';
        
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

    public function getByTeaGrade(Request $request)
    {
        $request->validate([
            'grade' => 'required|string'
        ]);

        $sellers = $this->service->getSellersByTeaGrade($request->grade);
        
        return response()->json($sellers);
    }

    private function getIndianStates()
{
    return [
        'Andhra Pradesh' => 'Andhra Pradesh',
        'Arunachal Pradesh' => 'Arunachal Pradesh',
        'Assam' => 'Assam',
        'Bihar' => 'Bihar',
        'Chhattisgarh' => 'Chhattisgarh',
        'Goa' => 'Goa',
        'Gujarat' => 'Gujarat',
        'Haryana' => 'Haryana',
        'Himachal Pradesh' => 'Himachal Pradesh',
        'Jharkhand' => 'Jharkhand',
        'Karnataka' => 'Karnataka',
        'Kerala' => 'Kerala',
        'Madhya Pradesh' => 'Madhya Pradesh',
        'Maharashtra' => 'Maharashtra',
        'Manipur' => 'Manipur',
        'Meghalaya' => 'Meghalaya',
        'Mizoram' => 'Mizoram',
        'Nagaland' => 'Nagaland',
        'Odisha' => 'Odisha',
        'Punjab' => 'Punjab',
        'Rajasthan' => 'Rajasthan',
        'Sikkim' => 'Sikkim',
        'Tamil Nadu' => 'Tamil Nadu',
        'Telangana' => 'Telangana',
        'Tripura' => 'Tripura',
        'Uttar Pradesh' => 'Uttar Pradesh',
        'Uttarakhand' => 'Uttarakhand',
        'West Bengal' => 'West Bengal',
        'Delhi' => 'Delhi',
        'Chandigarh' => 'Chandigarh',
        'Puducherry' => 'Puducherry',
        'Jammu and Kashmir' => 'Jammu and Kashmir',
        'Ladakh' => 'Ladakh'
    ];
}
}