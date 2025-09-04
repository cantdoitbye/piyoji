<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\DocumentType as ModelsDocumentType;
use App\Models\Garden;
use App\Models\GardenAttachment;
use App\Models\Poc;
use App\Services\GardenService;
use Dom\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

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

// Alternative approach: Clean the request data BEFORE validation

public function store(Request $request): RedirectResponse|JsonResponse
{
    // Log the incoming request
    \Log::info('Garden Store Request Started', [
        'user_id' => auth()->id(),
        'raw_invoice_variables' => $request->input('invoice_type_variables', [])
    ]);

    // CLEAN THE REQUEST DATA BEFORE VALIDATION
    $cleanedData = $request->all();
    
    // Clean invoice type variables to remove null/empty values
    if (isset($cleanedData['invoice_type_variables']) && is_array($cleanedData['invoice_type_variables'])) {
        foreach ($cleanedData['invoice_type_variables'] as $type => $variables) {
            if (is_array($variables)) {
                // Filter out null, empty string, and whitespace-only values
                $cleanedVariables = array_filter($variables, function($value) {
                    return $value !== null && 
                           $value !== '' && 
                           is_string($value) && 
                           trim($value) !== '';
                });
                
                // Reindex array and trim values
                $cleanedVariables = array_values(array_map('trim', $cleanedVariables));
                
                if (!empty($cleanedVariables)) {
                    $cleanedData['invoice_type_variables'][$type] = $cleanedVariables;
                } else {
                    unset($cleanedData['invoice_type_variables'][$type]);
                }
            }
        }
        
        // If no invoice type variables remain, set to empty array
        if (empty($cleanedData['invoice_type_variables'])) {
            $cleanedData['invoice_type_variables'] = [];
        }
    }
    
    // Clean POC IDs to ensure they're integers
    if (isset($cleanedData['poc_ids']) && is_array($cleanedData['poc_ids'])) {
        $cleanedData['poc_ids'] = array_filter(array_map('intval', $cleanedData['poc_ids']));
    }
    
   
  
    
    // Create a new request instance with cleaned data
    $cleanedRequest = new \Illuminate\Http\Request();
    $cleanedRequest->replace($cleanedData);
    $cleanedRequest->headers = $request->headers;
    
    // Updated validation rules (more permissive since we cleaned the data)
    $rules = [
        'garden_name' => 'required|string|max:255',
        'garden_type' => 'required|in:garden,mark',
        'address' => 'required|string',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'pincode' => 'nullable|string|max:10',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'tea_ids' => 'nullable|array|min:1',
        'tea_ids.*' => 'integer|exists:teas,id',
        'acceptable_invoice_types' => 'nullable|array',
        'acceptable_invoice_types.*' => 'string|in:fannings,brokens,dust',
        'invoice_type_variables' => 'nullable|array',
        'invoice_type_variables.*' => 'nullable|array',
        'invoice_type_variables.*.*' => 'string|min:1', // Now we know all values are clean strings
        'altitude' => 'nullable|numeric|min:0',
        'speciality' => 'nullable|string',
        'status' => 'nullable|boolean',
        'remarks' => 'nullable|string',
        'category_filters' => 'nullable|array',
        'category_filters.*.category' => 'required_with:category_filters|string',
        'category_filters.*.tea_types' => 'required_with:category_filters|array',
        'category_filters.*.grade_codes' => 'nullable|array',
        'poc_ids' => 'nullable|array',
        'poc_ids.*' => 'integer|exists:pocs,id'
    ];

    try {
        \Log::info('Starting validation with cleaned data');
        
        $validatedData = $cleanedRequest->validate($rules);
        
        \Log::info('Validation passed', [
            'validated_data_keys' => array_keys($validatedData),
            'final_invoice_variables' => $validatedData['invoice_type_variables'] ?? []
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed even with cleaned data', [
            'errors' => $e->errors(),
            'cleaned_data' => $cleanedData
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
        
        return back()->withInput()->withErrors($e->errors());
    }

    // Set default status if not provided
    if (!isset($validatedData['status'])) {
        $validatedData['status'] = true;
    }

    // The invoice type variables are already cleaned, so we can use them directly
    if (isset($validatedData['acceptable_invoice_types']) && isset($validatedData['invoice_type_variables'])) {
        $filteredVariables = [];
        foreach ($validatedData['acceptable_invoice_types'] as $type) {
            if (isset($validatedData['invoice_type_variables'][$type]) && !empty($validatedData['invoice_type_variables'][$type])) {
                $filteredVariables[$type] = $validatedData['invoice_type_variables'][$type];
            }
        }
        $validatedData['invoice_type_variables'] = $filteredVariables;
    } else {
        $validatedData['invoice_type_variables'] = [];
    }

    // Log the final data to be stored
    \Log::info('Final validated data prepared for storage', [
        'data_keys' => array_keys($validatedData),
        'garden_name' => $validatedData['garden_name'],
        'garden_type' => $validatedData['garden_type'],
        'invoice_variables_final' => $validatedData['invoice_type_variables']
    ]);

    try {
        \Log::info('Calling service store method');
        
        if (!$this->service) {
            throw new \Exception('Garden service not initialized');
        }
        
        $garden = $this->service->store($validatedData);
        
        \Log::info('Garden created successfully', [
            'garden_id' => $garden->id ?? 'unknown',
            'garden_name' => $garden->garden_name ?? 'unknown'
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $this->title . ' created successfully.',
                'data' => $garden,
                'redirect_url' => route($this->routePrefix . '.index')
            ]);
        }

        return redirect()
            ->route($this->routePrefix . '.index')
            ->with('success', $this->title . ' created successfully.');
            
    } catch (\Exception $e) {
        \Log::error('Garden creation failed', [
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'validated_data' => $validatedData
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating garden: ' . $e->getMessage()
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => 'Error creating garden: ' . $e->getMessage()]);
    }
}

// Add this debug method to check service
public function debugService()
{
    \Log::info('Garden Service Debug', [
        'service_exists' => isset($this->service),
        'service_class' => $this->service ? get_class($this->service) : 'null',
        'service_methods' => $this->service ? get_class_methods($this->service) : [],
        'controller_class' => get_class($this)
    ]);
    
    return response()->json([
        'service_exists' => isset($this->service),
        'service_class' => $this->service ? get_class($this->service) : 'null',
        'service_methods' => $this->service ? get_class_methods($this->service) : []
    ]);
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
            'garden_type' => 'required|in:garden,mark',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'tea_ids' => 'required|array|min:1',
            'tea_ids.*' => 'integer|exists:teas,id',
            'acceptable_invoice_types' => 'nullable|array',
            'acceptable_invoice_types.*' => 'string|in:fannings,brokens,dust',
            'invoice_type_variables' => 'nullable|array',
            'invoice_type_variables.fannings' => 'nullable|array',
            'invoice_type_variables.fannings.*' => 'string',
            'invoice_type_variables.brokens' => 'nullable|array',
            'invoice_type_variables.brokens.*' => 'string',
            'invoice_type_variables.dust' => 'nullable|array',
            'invoice_type_variables.dust.*' => 'string',
            'altitude' => 'nullable|numeric|min:0',
            'speciality' => 'nullable|string',
            'status' => 'boolean',
            'remarks' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        // Process invoice type variables to only include data for selected types
        if (isset($validatedData['acceptable_invoice_types']) && isset($validatedData['invoice_type_variables'])) {
            $filteredVariables = [];
            foreach ($validatedData['acceptable_invoice_types'] as $type) {
                if (isset($validatedData['invoice_type_variables'][$type])) {
                    // Remove empty values and ensure unique values
                    $variables = array_filter($validatedData['invoice_type_variables'][$type]);
                    $variables = array_unique($variables);
                    if (!empty($variables)) {
                        $filteredVariables[$type] = array_values($variables);
                    }
                }
            }
            $validatedData['invoice_type_variables'] = $filteredVariables;
        } else {
            $validatedData['invoice_type_variables'] = [];
        }

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
            $this->service->delete($id);

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

            return redirect()
                ->route($this->routePrefix . '.index')
                ->withErrors(['error' => $e->getMessage()]);
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


    /**
     * Show garden attachment management page
     */
    public function manageAttachments(int $id)
    {
        try {
            $garden = Garden::with(['attachments' => function($query) {
                $query->with(['uploadedByUser', 'verifiedByUser', 'documentType'])->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            $documentTypes = ModelsDocumentType::getActiveOptions();
            
            return view('admin.gardens.manage-attachments', compact('garden', 'documentTypes'));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.gardens.index')
                ->with('error', 'Garden not found.');
        }
    }

    /**
     * Upload additional attachments to existing garden
     */
    public function uploadAttachments(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attachments' => 'required|array|max:10',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,bmp,txt|max:10240',
            'document_type_ids' => 'nullable|array',
            'document_type_ids.*' => 'nullable|integer|exists:document_types,id',
            'attachment_descriptions' => 'nullable|array',
            'attachment_descriptions.*' => 'nullable|string|max:500'
        ]);

        try {
            $garden = Garden::findOrFail($id);
            $this->handleFileAttachments($garden, $request);

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully.',
                'attachments_count' => $garden->attachments()->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get garden attachments
     */
    public function getAttachments(int $id): JsonResponse
    {
        try {
            $garden = Garden::findOrFail($id);
            $attachments = $garden->attachments()
                ->with(['uploadedByUser', 'verifiedByUser', 'documentType'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'attachments' => $attachments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(int $gardenId, int $attachmentId): JsonResponse
    {
        try {
            $attachment = GardenAttachment::where('garden_id', $gardenId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(int $gardenId, int $attachmentId)
    {
        try {
            $attachment = GardenAttachment::where('garden_id', $gardenId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            if (!Storage::exists($attachment->file_path)) {
                abort(404, 'File not found');
            }

            return Storage::download($attachment->file_path, $attachment->file_name);

        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Preview attachment
     */
    public function previewAttachment(int $gardenId, int $attachmentId)
    {
        try {
            $attachment = GardenAttachment::where('garden_id', $gardenId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            if (!Storage::exists($attachment->file_path)) {
                abort(404, 'File not found');
            }

            return response(Storage::get($attachment->file_path))
                ->header('Content-Type', $attachment->file_type)
                ->header('Content-Disposition', 'inline; filename="' . $attachment->file_name . '"');

        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Verify attachment
     */
    public function verifyAttachment(int $gardenId, int $attachmentId): JsonResponse
    {
        try {
            $attachment = GardenAttachment::where('garden_id', $gardenId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            $attachment->verify();

            return response()->json([
                'success' => true,
                'message' => 'Attachment verified successfully.',
                'attachment' => $attachment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update attachment details
     */
    public function updateAttachment(Request $request, int $gardenId, int $attachmentId): JsonResponse
    {
        $request->validate([
            'document_type_id' => 'required|integer|exists:document_types,id',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $attachment = GardenAttachment::where('garden_id', $gardenId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            $attachment->update([
                'document_type_id' => $request->document_type_id,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attachment updated successfully.',
                'attachment' => $attachment->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }


    /**
     * Handle multiple file attachments for garden
     */
    private function handleFileAttachments(Garden $garden, Request $request)
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        $files = $request->file('attachments');
        $documentTypeIds = $request->input('document_type_ids', []);
        $descriptions = $request->input('attachment_descriptions', []);

        foreach ($files as $index => $file) {
            if ($file->isValid()) {
                // Generate unique filename
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                
                // Store file in garden-specific directory
                $path = $file->storeAs('gardens/' . $garden->id . '/attachments', $filename, 'public');

                // Create attachment record
                GardenAttachment::create([
                    'garden_id' => $garden->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'document_type_id' => $documentTypeIds[$index] ?? null,
                    'description' => $descriptions[$index] ?? null,
                    'uploaded_by' => auth()->id()
                ]);
            }
        }
    }
}