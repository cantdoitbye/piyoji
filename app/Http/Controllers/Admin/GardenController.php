<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Poc;
use App\Services\GardenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GardenController extends BaseAdminController
{
    protected $viewPrefix = 'admin.gardens';
    protected $routePrefix = 'admin.gardens';
    protected $title = 'Garden';

    public function __construct(GardenService $gardenService)
    {
        $this->service = $gardenService;
    }

    protected function getPermissionPrefix(): string
    {
        return 'gardens';
    }

    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'state', 'tea_id', 'per_page']);
        $gardens = $this->service->index($filters);
        $statistics = $this->service->getStatistics();

        if ($request->ajax()) {
            return response()->json([
                'data' => $gardens,
                'statistics' => $statistics
            ]);
        }


        return view($this->viewPrefix . '.index', [
            'gardens' => $gardens,
            'statistics' => $statistics,
            'filters' => $filters,
            'title' => $this->title . ' Management',
            'states' => $this->service->getStatesOptions(),
            'teas' => $this->service->getAvailableTeas(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function create(): View
    {
                $pocs = Poc::get();

        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'states' => $this->service->getStatesOptions(),
            'teas' => $this->service->getAvailableTeas(),
            'statusOptions' => $this->service->getStatusOptions(),
             'pocs' => $pocs

        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'garden_name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'tea_ids' => 'required|array|min:1',
            'tea_ids.*' => 'integer|exists:teas,id',
            'altitude' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $garden = $this->service->store($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $garden
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
        $garden = $this->service->show($id);

        if (!$garden) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }

        if (request()->ajax()) {
            return response()->json($garden);
        }

        return view($this->viewPrefix . '.show', [
            'garden' => $garden,
            'title' => $this->title . ' Details'
        ]);
    }

    public function edit(int $id): View
    {
        $garden = $this->service->show($id);

        if (!$garden) {
            abort(404);
        }

        return view($this->viewPrefix . '.edit', [
            'garden' => $garden,
            'title' => 'Edit ' . $this->title,
            'states' => $this->service->getStatesOptions(),
            'teas' => $this->service->getAvailableTeas(),
            'statusOptions' => $this->service->getStatusOptions()
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $rules = [
            'garden_name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'tea_ids' => 'required|array|min:1',
            'tea_ids.*' => 'integer|exists:teas,id',
            'altitude' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ];

        $validatedData = $request->validate($rules);

        try {
            $garden = $this->service->update($id, $validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully.',
                    'data' => $garden
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
            'garden_name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'tea_ids' => 'required|array|min:1',
            'tea_ids.*' => 'integer|exists:teas,id',
            'altitude' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }
    
    protected function validateUpdate(Request $request, int $id): array
    {
        return $request->validate([
            'garden_name' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'tea_ids' => 'required|array|min:1',
            'tea_ids.*' => 'integer|exists:teas,id',
            'altitude' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string'
        ]);
    }
}