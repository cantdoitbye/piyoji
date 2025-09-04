<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $documentTypes = DocumentType::ordered()->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $documentTypes
            ]);
        }

        return view('admin.document-types.index', compact('documentTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.document-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        try {
            $documentType = DocumentType::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->boolean('status', true),
                'sort_order' => $request->input('sort_order', 0)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document type created successfully.',
                    'data' => $documentType
                ]);
            }

            return redirect()
                ->route('admin.document-types.index')
                ->with('success', 'Document type created successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error creating document type: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentType $documentType): View|JsonResponse
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $documentType
            ]);
        }

        return view('admin.document-types.show', compact('documentType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentType $documentType): View
    {
        return view('admin.document-types.edit', compact('documentType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentType $documentType): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'description' => 'nullable|string|max:500',
            'status' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        try {
            $documentType->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->boolean('status', true),
                'sort_order' => $request->input('sort_order', $documentType->sort_order)
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document type updated successfully.',
                    'data' => $documentType->fresh()
                ]);
            }

            return redirect()
                ->route('admin.document-types.index')
                ->with('success', 'Document type updated successfully.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Error updating document type: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentType $documentType): RedirectResponse|JsonResponse
    {
        try {
            // Check if document type is in use
            $inUseCount = $documentType->buyerAttachments()->count() + 
                         $documentType->gardenAttachments()->count();
                         
            if ($inUseCount > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete document type as it is currently in use by attachments.'
                    ], 422);
                }
                
                return back()->with('error', 'Cannot delete document type as it is currently in use by attachments.');
            }

            $documentType->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document type deleted successfully.'
                ]);
            }

            return redirect()
                ->route('admin.document-types.index')
                ->with('success', 'Document type deleted successfully.');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Error deleting document type: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status of document type
     */
    public function toggleStatus(DocumentType $documentType): JsonResponse
    {
        try {
            $documentType->update(['status' => !$documentType->status]);

            return response()->json([
                'success' => true,
                'message' => 'Document type status updated successfully.',
                'status' => $documentType->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get active document types for dropdown
     */
    public function getActive(): JsonResponse
    {
        try {
            $documentTypes = DocumentType::getActiveOptions();

            return response()->json([
                'success' => true,
                'data' => $documentTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}