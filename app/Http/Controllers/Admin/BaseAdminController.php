<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

abstract class BaseAdminController extends Controller
{
    protected $service;
    protected $viewPrefix;
    protected $routePrefix;
    protected $title;
    
    // public function __construct()
    // {
    //     $this->middleware('auth:admin');
    //     $this->middleware('permission:' . $this->getPermissionPrefix() . '.view', ['only' => ['index', 'show']]);
    //     $this->middleware('permission:' . $this->getPermissionPrefix() . '.create', ['only' => ['create', 'store']]);
    //     $this->middleware('permission:' . $this->getPermissionPrefix() . '.edit', ['only' => ['edit', 'update']]);
    //     $this->middleware('permission:' . $this->getPermissionPrefix() . '.delete', ['only' => ['destroy']]);
    // }
    
    abstract protected function getPermissionPrefix(): string;
    
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['search', 'status', 'per_page']);
        $data = $this->service->index($filters);
        
        if ($request->ajax()) {
            return response()->json($data);
        }
        
        return view($this->viewPrefix . '.index', [
            'data' => $data,
            'title' => $this->title,
            'filters' => $filters
        ]);
    }
    
    public function create(): View
    {
        return view($this->viewPrefix . '.create', [
            'title' => 'Create ' . $this->title
        ]);
    }
    
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $this->validateStore($request);
        
        try {
            $item = $this->service->store($validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully',
                    'data' => $item
                ]);
            }
            
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' created successfully');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating ' . $this->title,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error creating ' . $this->title);
        }
    }
    
    public function show(int $id): View|JsonResponse
    {
        $item = $this->service->show($id);
        
        if (!$item) {
            if (request()->ajax()) {
                return response()->json(['error' => $this->title . ' not found'], 404);
            }
            abort(404);
        }
        
        if (request()->ajax()) {
            return response()->json($item);
        }
        
        return view($this->viewPrefix . '.show', [
            'item' => $item,
            'title' => $this->title . ' Details'
        ]);
    }
    
    public function edit(int $id): View
    {
        $item = $this->service->show($id);
        
        if (!$item) {
            abort(404);
        }
        
        return view($this->viewPrefix . '.edit', [
            'item' => $item,
            'title' => 'Edit ' . $this->title
        ]);
    }
    
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $this->validateUpdate($request, $id);
        
        try {
            $item = $this->service->update($id, $validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' updated successfully',
                    'data' => $item
                ]);
            }
            
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' updated successfully');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating ' . $this->title,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Error updating ' . $this->title);
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
            
            return redirect()->route($this->routePrefix . '.index')
                ->with('success', $this->title . ' deleted successfully');
                
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting ' . $this->title,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error deleting ' . $this->title);
        }
    }
    
    abstract protected function validateStore(Request $request): array;
    abstract protected function validateUpdate(Request $request, int $id): array;
}