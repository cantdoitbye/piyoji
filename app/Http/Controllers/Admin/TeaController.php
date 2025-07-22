<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
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


    public function show(int $id): View|JsonResponse
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
}