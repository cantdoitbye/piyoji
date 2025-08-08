<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\Buyer;
use App\Models\BuyerAttachment;
use App\Models\Poc;
use App\Services\BuyerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

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
            $pocs = Poc::active()->forBuyers()->orderBy('poc_name')->get(); 

        return view($this->viewPrefix . '.create', [
            'title' => 'Add New ' . $this->title,
            'buyerTypes' => $this->service->getBuyerTypeOptions(),
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'pocs' => $pocs
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
    $pocs = Poc::active()->forBuyers()->orderBy('poc_name')->get();

        return view($this->viewPrefix . '.edit', [
            'buyer' => $buyer,
            'title' => 'Edit ' . $this->title,
            'buyerTypes' => $this->service->getBuyerTypeOptions(),
            'teaGrades' => $this->service->getTeaGradeOptions(),
            'statusOptions' => $this->service->getStatusOptions(),
            'pocs' => $pocs
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
             'poc_ids' => 'nullable|array',
        'poc_ids.*' => 'exists:pocs,id',
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

     public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'buyer_name' => 'required|string|max:255',
            'buyer_type' => 'required|in:big,small',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:buyers,email',
            'phone' => 'required|string|max:20',
            'billing_address' => 'required|string',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_pincode' => 'required|string|max:10',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_pincode' => 'required|string|max:10',
            'preferred_tea_grades' => 'required|array|min:1',
            'preferred_tea_grades.*' => 'string',
            'status' => 'boolean',
            'poc_ids' => 'nullable|array',
            'poc_ids.*' => 'integer|exists:pocs,id',
            'remarks' => 'nullable|string',
            
            // File attachments validation
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,bmp,txt|max:10240', // 10MB max
            'attachment_types' => 'nullable|array',
            'attachment_types.*' => 'string|in:license,agreement,certificate,registration,tax_document,bank_statement,other',
            'attachment_descriptions' => 'nullable|array',
            'attachment_descriptions.*' => 'nullable|string|max:500'
        ];

        $validatedData = $request->validate($rules);

        try {
            $buyer = $this->service->store($validatedData);

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                $this->handleFileAttachments($buyer, $request);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->title . ' created successfully.',
                    'data' => $buyer
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

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    /**
 * Show buyer attachment management page
 */
public function manageAttachments(int $id)
{
    try {
        $buyer = Buyer::with(['attachments' => function($query) {
            $query->with(['uploadedByUser', 'verifiedByUser'])->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $documentTypes = BuyerAttachment::getDocumentTypeOptions();
        
        return view('admin.buyers.manage-attachments', compact('buyer', 'documentTypes'));

    } catch (\Exception $e) {
        return redirect()
            ->route('admin.buyers.index')
            ->with('error', 'Buyer not found.');
    }
}

    /**
     * Handle multiple file attachments for buyer
     */
    private function handleFileAttachments(Buyer $buyer, Request $request)
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        $files = $request->file('attachments');
        $types = $request->input('attachment_types', []);
        $descriptions = $request->input('attachment_descriptions', []);

        foreach ($files as $index => $file) {
            if ($file->isValid()) {
                // Generate unique filename
                $filename = time() . '_' . $index . '_' . $file->getClientOriginalName();
                
                // Store file in buyer-specific directory
                $path = $file->storeAs('buyers/' . $buyer->id . '/attachments', $filename, 'public');

                // Create attachment record
                BuyerAttachment::create([
                    'buyer_id' => $buyer->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'document_type' => $types[$index] ?? 'other',
                    'description' => $descriptions[$index] ?? null,
                    'uploaded_by' => auth()->id()
                ]);
            }
        }
    }

    /**
     * Upload additional attachments to existing buyer
     */
    public function uploadAttachments(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attachments' => 'required|array|max:10',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,bmp,txt|max:10240',
            'attachment_types' => 'nullable|array',
            'attachment_types.*' => 'string|in:license,agreement,certificate,registration,tax_document,bank_statement,other',
            'attachment_descriptions' => 'nullable|array',
            'attachment_descriptions.*' => 'nullable|string|max:500'
        ]);

        try {
            $buyer = Buyer::findOrFail($id);
            $this->handleFileAttachments($buyer, $request);

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully.',
                'attachments_count' => $buyer->attachments()->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get buyer attachments
     */
    public function getAttachments(int $id): JsonResponse
    {
        try {
            $buyer = Buyer::findOrFail($id);
            $attachments = $buyer->attachments()
                ->with(['uploadedByUser', 'verifiedByUser'])
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
    public function deleteAttachment(int $buyerId, int $attachmentId): JsonResponse
    {
        try {
            $attachment = BuyerAttachment::where('buyer_id', $buyerId)
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
    public function downloadAttachment(int $buyerId, int $attachmentId): Response
    {
        try {
            $attachment = BuyerAttachment::where('buyer_id', $buyerId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            if (!Storage::disk('public')->exists($attachment->file_path)) {
                abort(404, 'File not found');
            }

            return response()
                ->download(
                    Storage::disk('public')->path($attachment->file_path),
                    $attachment->file_name
                );

        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Preview attachment (for images and PDFs)
     */
    public function previewAttachment(int $buyerId, int $attachmentId): Response
    {
        try {
            $attachment = BuyerAttachment::where('buyer_id', $buyerId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            if (!Storage::disk('public')->exists($attachment->file_path)) {
                abort(404, 'File not found');
            }

            $file = Storage::disk('public')->get($attachment->file_path);

            return response($file, 200)
                ->header('Content-Type', $attachment->file_type)
                ->header('Content-Disposition', 'inline; filename="' . $attachment->file_name . '"');

        } catch (\Exception $e) {
            abort(404, 'File not found');
        }
    }

    /**
     * Verify attachment
     */
    public function verifyAttachment(int $buyerId, int $attachmentId): JsonResponse
    {
        try {
            $attachment = BuyerAttachment::where('buyer_id', $buyerId)
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
    public function updateAttachment(Request $request, int $buyerId, int $attachmentId): JsonResponse
    {
        $request->validate([
            'document_type' => 'required|string|in:license,agreement,certificate,registration,tax_document,bank_statement,other',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $attachment = BuyerAttachment::where('buyer_id', $buyerId)
                ->where('id', $attachmentId)
                ->firstOrFail();

            $attachment->update([
                'document_type' => $request->document_type,
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
}