<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Services\PocService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class PocController extends BaseAdminController
{
    protected $viewPrefix = 'admin.pocs';
    protected $routePrefix = 'admin.pocs';
    protected $title = 'POC';

    public function __construct(PocService $pocService)
    {
        $this->service = $pocService;
    }

    protected function getPermissionPrefix(): string
    {
        return 'pocs';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'poc_type', 'state', 'per_page']);
        $pocs = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $pocs,
                'statistics' => $statistics
            ]);
        }

        return view($this->viewPrefix . '.index', [
            'pocs' => $pocs,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'pocTypes' => $this->service->getPocTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'types' => $this->service->getTypeOptions(),
            'pocTypes' => $this->service->getPocTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'poc_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pocs,email',
            'phone' => 'required|string|max:20',
            'designation' => 'nullable|string|max:255',
            'type' => 'required|in:poc,tester',
            'poc_type' => 'required|in:seller,buyer,both',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $poc = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $poc
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
        $poc = $this->service->show($id);

        if (!$poc) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($poc);
        }

        return view($this->viewPrefix . '.show', [
            'poc' => $poc,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $poc = $this->service->show($id);

        if (!$poc) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'poc' => $poc,
            'title' => 'Edit ' . $this->title,
            'pocTypes' => $this->service->getPocTypeOptions(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $rules = [
            'poc_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('pocs', 'email')->ignore($id)
            ],
            'phone' => 'required|string|max:20',
            'designation' => 'nullable|string|max:255',
            'poc_type' => 'required|in:seller,buyer,both',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $poc = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully.',
                    'data' => $poc
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
            'poc_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:pocs,email',
            'phone' => 'required|string|max:20',
            'designation' => 'nullable|string|max:255',
            'poc_type' => 'required|in:seller,buyer,both',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }
    
    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'poc_name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:pocs,email,{$id}",
            'phone' => 'required|string|max:20',
            'designation' => 'nullable|string|max:255',
            'poc_type' => 'required|in:seller,buyer,both',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }
}