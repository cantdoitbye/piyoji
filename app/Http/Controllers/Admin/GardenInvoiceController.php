<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Garden;
use App\Models\GardenInvoice;
use App\Models\GardenInvoiceSample;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GardenInvoiceController extends Controller
{
    /**
     * Display invoices for a specific garden
     */
    public function index(Garden $garden, Request $request): View|JsonResponse
    {
        $filters = $request->only(['status', 'search', 'date_from', 'date_to']);
        
        $invoices = GardenInvoice::where('garden_id', $garden->id)
            ->with(['creator', 'samples'])
            ->when($filters['status'] ?? null, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('mark_name', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                return $query->whereDate('packaging_date', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                return $query->whereDate('packaging_date', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statistics = $this->getInvoiceStatistics($garden->id);

        if ($request->ajax()) {
            return response()->json([
                'invoices' => $invoices,
                'statistics' => $statistics
            ]);
        }

        return view('admin.garden-invoices.index', compact('garden', 'invoices', 'statistics', 'filters'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(Garden $garden): View
    {
        // $invoicePrefixes = $this->getInvoicePrefixes($garden);
        
         // return view('admin.garden-invoices.create', compact('garden', 'invoicePrefixes'));
         $invoicePrefixes = $this->getInvoicePrefixes($garden);
    
    // Get garden's acceptable invoice types and their variables
    $gardenInvoiceTypes = $garden->acceptable_invoice_types ?? [];
    $gardenVariables = $garden->invoice_type_variables ?? [];
    
    // Get available grades from garden's category filters
    $availableGrades = $this->getAvailableGrades($garden);
    
    return view('admin.garden-invoices.create', compact(
        'garden', 
        'invoicePrefixes', 
        'gardenInvoiceTypes', 
        'gardenVariables',
        'availableGrades'
    ));
   
    }

    /**
     * Store a newly created invoice with samples
     */
    // public function store(Garden $garden, Request $request): RedirectResponse|JsonResponse
    // {
    //     $validatedData = $request->validate([
    //         'invoice_prefix' => 'required|string|max:10',
    //         'bags_packages' => 'required|integer|min:0',
    //         'packaging_date' => 'required|date',
    //         'notes' => 'nullable|string|max:1000',
    //         'samples' => 'required|array|min:1',
    //         // 'samples.*.sample_code' => 'nullable|string|max:50',
    //         'samples.*.sample_weight' => 'required|numeric|min:0.001|max:999999.999',
    //         'samples.*.number_of_sets' => 'required|integer|min:1',
    //         'samples.*.sample_notes' => 'nullable|string|max:500'
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // Generate invoice number
    //         $invoiceNumber = GardenInvoice::generateInvoiceNumber($validatedData['invoice_prefix']);

    //         // Create invoice
    //         $invoice = GardenInvoice::create([
    //             'garden_id' => $garden->id,
    //             'mark_name' => $garden->garden_name,
    //             'invoice_prefix' => $validatedData['invoice_prefix'],
    //             'invoice_number' => $invoiceNumber,
    //             'bags_packages' => $validatedData['bags_packages'],
    //             'packaging_date' => $validatedData['packaging_date'],
    //             'notes' => $validatedData['notes'],
    //             'created_by' => auth()->id()
    //         ]);

    //         // Create samples
    //         foreach ($validatedData['samples'] as $sampleData) {
    //             $invoice->addSample([
    //                 'sample_code' => $sampleData['sample_code'],
    //                 'sample_weight' => $sampleData['sample_weight'],
    //                 'number_of_sets' => $sampleData['number_of_sets'],
    //                 'sample_notes' => $sampleData['sample_notes']
    //             ]);
    //         }

    //         // Update total weight (will be calculated from samples)
    //         $invoice->updateTotalWeight();

    //         DB::commit();

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Invoice created successfully.',
    //                 'invoice' => $invoice->load('samples')
    //             ]);
    //         }

    //         return redirect()
    //             ->route('admin.gardens.invoices.index', $garden)
    //             ->with('success', 'Invoice created successfully with ' . count($validatedData['samples']) . ' samples.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
            
    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Error creating invoice: ' . $e->getMessage()
    //             ], 422);
    //         }

    //         return back()
    //             ->withInput()
    //             ->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()]);
    //     }
    // }


    public function store(Garden $garden, Request $request): RedirectResponse|JsonResponse
{
    $validatedData = $request->validate([
        'invoice_number' => 'required|string|max:50|unique:garden_invoices,invoice_number', // Manual invoice number
        'invoice_prefix' => 'required|string|max:10',
        'category_type' => 'required|string|in:fannings,brokens,dust', // New field
        'variables' => 'required|array|min:1', // Selected variables for the category
        'variables.*' => 'string',
        'grade' => 'required|string|max:50', // New grade field
        'bags_packages' => 'required|integer|min:0',
        'packaging_date' => 'required|date',
        'notes' => 'nullable|string|max:1000',
        'samples' => 'required|array|min:1',
        'samples.*.sample_weight' => 'required|numeric|min:0.001|max:999999.999',
        'samples.*.number_of_sets' => 'required|integer|min:1',
        'samples.*.sample_notes' => 'nullable|string|max:500'
        // Remove sample_code from validation as it will be internal
    ]);

    // Validate that selected category type is acceptable for this garden
    if (!$garden->acceptsInvoiceType($validatedData['category_type'])) {
        return back()->withErrors(['category_type' => 'Selected category type is not acceptable for this garden.']);
    }

    // Validate that selected variables are valid for the category type
    $gardenVariables = $garden->getVariablesForType($validatedData['category_type']);
    foreach ($validatedData['variables'] as $variable) {
        if (!in_array($variable, $gardenVariables)) {
            return back()->withErrors(['variables' => "Variable '{$variable}' is not valid for selected category type."]);
        }
    }

    try {
        DB::beginTransaction();

        // Create invoice with new fields
        $invoice = GardenInvoice::create([
            'garden_id' => $garden->id,
            'mark_name' => $garden->garden_name,
            'invoice_prefix' => $validatedData['invoice_prefix'],
            'invoice_number' => $validatedData['invoice_number'], // Manual invoice number
            'category_type' => $validatedData['category_type'],
            'variables' => $validatedData['variables'],
            'grade' => $validatedData['grade'],
            'bags_packages' => $validatedData['bags_packages'],
            'packaging_date' => $validatedData['packaging_date'],
            'notes' => $validatedData['notes'],
            'created_by' => auth()->id()
        ]);

        // Create samples with auto-generated sample codes (internal)
        foreach ($validatedData['samples'] as $index => $sampleData) {
            $sampleCode = $this->generateSampleCode($invoice, $index + 1);
            
            $invoice->addSample([
                'sample_code' => $sampleCode, // Auto-generated, hidden from form
                'sample_weight' => $sampleData['sample_weight'],
                'number_of_sets' => $sampleData['number_of_sets'],
                'sample_notes' => $sampleData['sample_notes']
            ]);
        }

        $invoice->updateTotalWeight();
        DB::commit();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'invoice' => $invoice->load('samples')
            ]);
        }

        return redirect()
            ->route('admin.gardens.invoices.index', $garden)
            ->with('success', 'Invoice created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => 'Error creating invoice: ' . $e->getMessage()]);
    }
}


/**
 * Get available grades from garden's category filters
 */
private function getAvailableGrades(Garden $garden)
{
    $grades = [];
    
    if ($garden->category_filters) {
        foreach ($garden->category_filters as $filter) {
            if (isset($filter['grade_codes']) && is_array($filter['grade_codes'])) {
                $grades = array_merge($grades, $filter['grade_codes']);
            }
        }
    }
    
    return array_unique($grades);
}


/**
 * Generate internal sample code
 */
private function generateSampleCode(GardenInvoice $invoice, int $sampleNumber)
{
    return $invoice->invoice_number . '-S' . str_pad($sampleNumber, 2, '0', STR_PAD_LEFT);
}

    /**
     * Display the specified invoice with samples
     */
    public function show(Garden $garden, GardenInvoice $invoice): View
    {
        $invoice->load(['creator', 'samples']);
        $samplesSummary = $invoice->getSamplesSummary();
        
        return view('admin.garden-invoices.show', compact('garden', 'invoice', 'samplesSummary'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Garden $garden, GardenInvoice $invoice): View
    {
        if (!$invoice->canEdit()) {
            abort(403, 'This invoice cannot be edited.');
        }

        $invoice->load('samples');
        $invoicePrefixes = $this->getInvoicePrefixes($garden);
        
        return view('admin.garden-invoices.edit', compact('garden', 'invoice', 'invoicePrefixes'));
    }

    /**
     * Update the specified invoice and its samples
     */
    public function update(Garden $garden, GardenInvoice $invoice, Request $request): RedirectResponse|JsonResponse
    {
        if (!$invoice->canEdit()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'This invoice cannot be edited.'], 403);
            }
            return back()->withErrors(['error' => 'This invoice cannot be edited.']);
        }

        $validatedData = $request->validate([
            'invoice_prefix' => 'required|string|max:10',
            'bags_packages' => 'required|integer|min:0',
            'packaging_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'samples' => 'required|array|min:1',
            'samples.*.id' => 'nullable|exists:garden_invoice_samples,id',
            'samples.*.sample_code' => 'nullable|string|max:50',
            'samples.*.sample_weight' => 'required|numeric|min:0.001|max:999999.999',
            'samples.*.number_of_sets' => 'required|integer|min:1',
            'samples.*.sample_notes' => 'nullable|string|max:500',
            'samples.*._delete' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Regenerate invoice number if prefix changed
            if ($invoice->invoice_prefix !== $validatedData['invoice_prefix']) {
                $validatedData['invoice_number'] = GardenInvoice::generateInvoiceNumber($validatedData['invoice_prefix']);
            }

            // Update invoice
            $invoice->update([
                'invoice_prefix' => $validatedData['invoice_prefix'],
                'invoice_number' => $validatedData['invoice_number'] ?? $invoice->invoice_number,
                'bags_packages' => $validatedData['bags_packages'],
                'packaging_date' => $validatedData['packaging_date'],
                'notes' => $validatedData['notes']
            ]);

            // Handle samples
            $existingSampleIds = [];
            foreach ($validatedData['samples'] as $sampleData) {
                if (isset($sampleData['_delete']) && $sampleData['_delete']) {
                    // Delete sample if marked for deletion
                    if (isset($sampleData['id'])) {
                        $invoice->removeSample($sampleData['id']);
                    }
                    continue;
                }

                if (isset($sampleData['id']) && $sampleData['id']) {
                    // Update existing sample
                    $sample = $invoice->samples()->find($sampleData['id']);
                    if ($sample) {
                        $sample->update([
                            'sample_code' => $sampleData['sample_code'],
                            'sample_weight' => $sampleData['sample_weight'],
                            'number_of_sets' => $sampleData['number_of_sets'],
                            'sample_notes' => $sampleData['sample_notes']
                        ]);
                        $existingSampleIds[] = $sample->id;
                    }
                } else {
                    // Create new sample
                    $newSample = $invoice->addSample([
                        'sample_code' => $sampleData['sample_code'],
                        'sample_weight' => $sampleData['sample_weight'],
                        'number_of_sets' => $sampleData['number_of_sets'],
                        'sample_notes' => $sampleData['sample_notes']
                    ]);
                    $existingSampleIds[] = $newSample->id;
                }
            }

            // Update total weight
            $invoice->updateTotalWeight();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice updated successfully.',
                    'invoice' => $invoice->fresh()->load('samples')
                ]);
            }

            return redirect()
                ->route('admin.gardens.invoices.show', [$garden, $invoice])
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating invoice: ' . $e->getMessage()
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Error updating invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Finalize the invoice
     */
    public function finalize(Garden $garden, GardenInvoice $invoice): RedirectResponse|JsonResponse
    {
        if (!$invoice->canFinalize()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'This invoice cannot be finalized.'], 403);
            }
            return back()->withErrors(['error' => 'This invoice cannot be finalized.']);
        }

        $invoice->update(['status' => GardenInvoice::STATUS_FINALIZED]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice finalized successfully.'
            ]);
        }

        return back()->with('success', 'Invoice finalized successfully.');
    }

    /**
     * Cancel the invoice
     */
    public function cancel(Garden $garden, GardenInvoice $invoice): RedirectResponse|JsonResponse
    {
        if (!$invoice->canCancel()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'This invoice cannot be cancelled.'], 403);
            }
            return back()->withErrors(['error' => 'This invoice cannot be cancelled.']);
        }

        $invoice->update(['status' => GardenInvoice::STATUS_CANCELLED]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully.'
            ]);
        }

        return back()->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Garden $garden, GardenInvoice $invoice): RedirectResponse|JsonResponse
    {
        if (!$invoice->canEdit()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'This invoice cannot be deleted.'], 403);
            }
            return back()->withErrors(['error' => 'This invoice cannot be deleted.']);
        }

        $invoice->delete(); // This will cascade delete samples

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.'
            ]);
        }

        return redirect()
            ->route('admin.gardens.invoices.index', $garden)
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Get invoice statistics for a garden
     */
    private function getInvoiceStatistics($gardenId): array
    {
        $invoices = GardenInvoice::where('garden_id', $gardenId);

        return [
            'total_invoices' => $invoices->count(),
            'draft_invoices' => $invoices->clone()->draft()->count(),
            'finalized_invoices' => $invoices->clone()->finalized()->count(),
            'cancelled_invoices' => $invoices->clone()->cancelled()->count(),
            'total_weight' => $invoices->clone()->finalized()->sum('total_invoice_weight'),
            'total_packages' => $invoices->clone()->finalized()->sum('bags_packages'),
            'total_samples' => GardenInvoiceSample::whereHas('invoice', function($q) use ($gardenId) {
                $q->where('garden_id', $gardenId)->finalized();
            })->count(),
        ];
    }

    /**
     * Get available invoice prefixes for a garden
     */
    private function getInvoicePrefixes(Garden $garden): array
    {
        $year = date('Y');
        $initials = $this->getInitials($garden->garden_name);
        
        return [
            $initials . $year => $initials . $year,
            $initials . substr($year, -2) => $initials . substr($year, -2),
            'INV' . $year => 'INV' . $year,
            'INV' . substr($year, -2) => 'INV' . substr($year, -2),
        ];
    }

    /**
     * Get initials from garden name
     */
    private function getInitials(string $name): string
    {
        $words = explode(' ', strtoupper($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= substr($word, 0, 1);
            }
        }
        
        return substr($initials, 0, 3);
    }
}