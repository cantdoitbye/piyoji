<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Tea;
use App\Services\TeaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TeaController extends BaseAdminController
{
    protected $viewPrefix = 'admin.teas';
    protected $routePrefix = 'admin.teas';
    protected $title = 'Tea';

    public function __construct(TeaService $teaService)
    {
        $this->service = $teaService;
    }

    protected function getPermissionPrefix(): string
    {
        return 'teas';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'category', 'tea_type', 'grade', 'per_page']);
        $teas = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $teas,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'teas' => $teas,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'categories' => $this->service->getCategoryOptions(),
            'teaTypes' => $this->service->getTeaTypeOptions(),
            'grades' => $this->service->getGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

   

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'categories' => $this->service->getCategoryOptions(),
            'teaTypes' => $this->service->getTeaTypeOptions(),
            'grades' => $this->service->getGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'category' => 'required|string|max:255',
            'tea_type' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'description' => 'nullable|string',
            'characteristics' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $tea = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $tea
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

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show($id): View|JsonResponse
    {
        $tea = $this->service->show($id);

        if (!$tea) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($tea);
        }

        return view($this->viewPrefix . '.show', [
            'tea' => $tea,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $tea = $this->service->show($id);

        if (!$tea) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'tea' => $tea,
            'title' => 'Edit ' . $this->title,
            'categories' => $this->service->getCategoryOptions(),
            'teaTypes' => $this->service->getTeaTypeOptions(),
            'grades' => $this->service->getGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $rules = [
            'category' => 'required|string|max:255',
            'tea_type' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'description' => 'nullable|string',
            'characteristics' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $tea = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully.',
                    'data' => $tea
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

            return back()
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
                    'message' => $this->title . ' deleted successfully.'
                ]);
            }

            return redirect()
                ->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
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

     protected function validateStore(Request $request): array
    {
        return $request->validate([
            'category' => 'required|string|max:255',
            'tea_type' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'description' => 'nullable|string',
            'characteristics' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }
    
    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'category' => 'required|string|max:255',
            'tea_type' => 'required|string|max:255',
            'sub_title' => 'required|string|max:255',
            'grade' => 'required|string|max:255',
            'description' => 'nullable|string',
            'characteristics' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }

  public function getTeaTypesByCategory(Request $request): JsonResponse
{
    $request->validate([
        'category' => 'required|string'
    ]);



    $category = $request->get('category');
    $teaTypes = Tea::getTeaTypesByCategory($category);
    
    // Fix: Check if teaTypes is empty and handle array_combine properly
    if (empty($teaTypes)) {
        return response()->json([
            'tea_types' => []
        ]);
    }
    
    return response()->json([
        'tea_types' => $teaTypes // Remove array_combine as it's already an associative array
    ]);
}

public function getGradeCodesByTeaType(Request $request)
{
    $teaType = $request->get('tea_type');
    $gradeCodes = Tea::getGradeCodesByTeaType($teaType);
    
    return response()->json([
        'grade_codes' => $gradeCodes
    ]);
}

public function getFilteredTeas(Request $request)
{
    $query = Tea::active()
        ->where('category_id', $request->get('category'))
        ->where('tea_type_id', $request->get('tea_type'));
    
    if ($request->has('grade_codes') && !empty($request->get('grade_codes'))) {
        $query->whereIn('grade_code', $request->get('grade_codes'));
    }
    
    $teas = $query->select('id', 'category_id', 'tea_type_id', 'sub_tea_type_id', 'grade_code')
                  ->get()
                  ->map(function($tea) {
                      return [
                          'id' => $tea->id,
                          'full_name' => $tea->full_name
                      ];
                  });
    
    return response()->json(['teas' => $teas]);
}

// Add this method to your TeaController class

/**
 * Get existing grade codes from database for given tea types (for Garden filtering)
 */
public function getExistingGradeCodesByTeaTypes(Request $request): JsonResponse
{
    try {
        $request->validate([
            'tea_types' => 'required|array',
            'tea_types.*' => 'string'
        ]);

        $teaTypes = $request->get('tea_types');
        \Log::info('getExistingGradeCodesByTeaTypes called with tea types: ' . json_encode($teaTypes));
        
        $gradeCodes = Tea::getExistingGradeCodesByTeaTypes($teaTypes);
        \Log::info('Found grade codes: ' . json_encode($gradeCodes));
        
        return response()->json([
            'success' => true,
            'grade_codes' => $gradeCodes,
            'count' => count($gradeCodes)
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in getExistingGradeCodesByTeaTypes: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'grade_codes' => []
        ], 500);
    }
}

// Add this method to your TeaController class

/**
 * Get filtered teas for multiple filter combinations (for Garden multi-category selection)
 */
public function getFilteredTeasMultiple(Request $request): JsonResponse
{
    try {
        $request->validate([
            'filters' => 'required|array|min:1',
            'filters.*.categories' => 'required|array|min:1',
            'filters.*.categories.*' => 'string',
            'filters.*.tea_types' => 'required|array|min:1', 
            'filters.*.tea_types.*' => 'string',
            'filters.*.sub_tea_types' => 'nullable|array',
            'filters.*.sub_tea_types.*' => 'string',
            'filters.*.grade_codes' => 'nullable|array',
            'filters.*.grade_codes.*' => 'string'
        ]);

        $filters = $request->get('filters');
        \Log::info('getFilteredTeasMultiple called with filters: ' . json_encode($filters));
        
        $allTeas = collect();

        foreach ($filters as $filter) {
            $categories = $filter['categories'];
            $teaTypes = $filter['tea_types'];
            $subTeaTypes = $filter['sub_tea_types'] ?? [];
            $gradeCodes = $filter['grade_codes'] ?? [];
            
            \Log::info('Processing filter - Categories: ' . json_encode($categories) . ', Tea Types: ' . json_encode($teaTypes));
            
            $filteredTeas = Tea::getFilteredTeas($categories, $teaTypes, $gradeCodes, $subTeaTypes);
            $allTeas = $allTeas->merge($filteredTeas);
        }

        // Remove duplicates based on tea ID
        $uniqueTeas = $allTeas->unique('id')->values();
        
        \Log::info('Found ' . $uniqueTeas->count() . ' unique teas after filtering');
        
        return response()->json([
            'success' => true,
            'teas' => $uniqueTeas,
            'count' => $uniqueTeas->count()
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in getFilteredTeasMultiple: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'teas' => [],
            'count' => 0
        ], 422);
    }
}
}