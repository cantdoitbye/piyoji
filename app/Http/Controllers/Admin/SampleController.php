<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesRegister;
use App\Services\SampleService;
use App\Services\SellerService;
use App\Services\BatchService;
use App\Models\Sample;
use App\Models\SampleAllocation;
use App\Services\SalesRegisterService;
use App\Services\SampleTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SampleController extends Controller
{
    public function __construct(
        protected SampleService $sampleService,
        protected SellerService $sellerService,
        protected BatchService $batchService,
        protected SalesRegisterService $salesRegisterService  // Add this line

    ) {}

    /**
     * Display samples listing with batch information
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'evaluation_status' => $request->get('evaluation_status'),
            'batch_status' => $request->get('batch_status'),
            'seller_id' => $request->get('seller_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15)
        ];

        $samples = $this->sampleService->getAllSamples($filters);
        $statistics = $this->sampleService->getSampleStatistics();
        $sellers = $this->sellerService->getForSelect();

        // Get today's stats for the form
        $todayStats = [
            'samples' => $statistics['today'],
            'unbatched' => $statistics['today_unbatched']
        ];

        return view('admin.samples.index', compact('samples', 'statistics', 'sellers', 'filters', 'todayStats'));
    }

    /**
     * Show the form for creating a new sample
     */
    public function create()
    {
        $sellers = $this->sellerService->getForSelect();
        $todayStats = [
            'samples' => Sample::whereDate('arrival_date', Carbon::today())->count(),
            'unbatched' => Sample::whereDate('arrival_date', Carbon::today())
                ->whereNull('batch_group_id')->count()
        ];

        return view('admin.samples.create', compact('sellers', 'todayStats'));
    }

    /**
     * Store a newly created sample
     */
    public function store(Request $request)
    {
        $validator = $this->validateStore($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $sample = $this->sampleService->createSample($validator->validated());
            
            return redirect()->route('admin.samples.index')
                ->with('success', 'Sample added successfully! Sample ID: ' . $sample->sample_id);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified sample
     */
    public function show(int $id)
    {
        try {
            $sample = $this->sampleService->getSampleById($id);
            
            if (!$sample) {
                return redirect()->route('admin.samples.index')
                    ->with('error', 'Sample not found');
            }

            return view('admin.samples.show', compact('sample'));
        } catch (\Exception $e) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Error loading sample: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified sample
     */
    public function edit(int $id)
    {
        try {
            $sample = $this->sampleService->getSampleById($id);
            
            if (!$sample) {
                return redirect()->route('admin.samples.index')
                    ->with('error', 'Sample not found');
            }

            $sellers = $this->sellerService->getForSelect();
            
            return view('admin.samples.edit', compact('sample', 'sellers'));
        } catch (\Exception $e) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Error loading sample: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified sample
     */
    public function update(Request $request, int $id)
    {
        $validator = $this->validateUpdate($request, $id);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $sample = $this->sampleService->updateSample($id, $validator->validated());
            
            return redirect()->route('admin.samples.show', $id)
                ->with('success', 'Sample updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified sample
     */
    public function destroy(int $id)
    {
        try {
            $this->sampleService->deleteSample($id);
            
            return redirect()->route('admin.samples.index')
                ->with('success', 'Sample deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show evaluation form
     */
    public function evaluate(int $id)
    {
        try {
            $sample = $this->sampleService->getSampleById($id);
            
            if (!$sample) {
                return redirect()->route('admin.samples.index')
                    ->with('error', 'Sample not found');
            }

            if ($sample->evaluation_status === Sample::EVALUATION_COMPLETED) {
                return redirect()->route('admin.samples.show', $id)
                    ->with('info', 'Sample has already been evaluated');
            }

            return view('admin.samples.evaluate', compact('sample'));
        } catch (\Exception $e) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Error loading sample: ' . $e->getMessage());
        }
    }

    /**
     * Store evaluation results
     */
    public function storeEvaluation(Request $request, int $id)
    {
        $validator = $this->validateEvaluation($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $sample = $this->sampleService->storeEvaluation($id, $validator->validated(), Auth::id());
            
            return redirect()->route('admin.samples.show', $id)
                ->with('success', 'Sample evaluation completed successfully! Overall Score: ' . $sample->overall_score);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Start evaluation process
     */
    public function startEvaluation(int $id)
    {
        try {
            $sample = $this->sampleService->startEvaluation($id, Auth::id());
            
            return redirect()->route('admin.samples.evaluate', $id)
                ->with('success', 'Evaluation started. You can now proceed with scoring.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show pending evaluations
     */
    public function pendingEvaluations()
    {
        $samples = $this->sampleService->getPendingEvaluationSamples();
        
        return view('admin.samples.pending-evaluations', compact('samples'));
    }

    /**
     * Show evaluated samples
     */
    public function evaluatedSamples()
    {
        $samples = $this->sampleService->getEvaluatedSamples();
        
        return view('admin.samples.evaluated', compact('samples'));
    }

    /**
     * Show approved samples
     */
    public function approvedSamples()
    {
        $samples = $this->sampleService->getApprovedSamples();
        
        return view('admin.samples.approved', compact('samples'));
    }

    /**
     * Show tasting report
     */
    public function tastingReport(Request $request)
    {
        $filters = [
            'seller_id' => $request->get('seller_id'),
            'min_score' => $request->get('min_score'),
            'status' => $request->get('status'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date')
        ];

        $report = $this->sampleService->generateTastingReport($filters);
        $sellers = $this->sellerService->getForSelect();
        
        return view('admin.samples.tasting-report', compact('report', 'sellers', 'filters'));
    }

    /**
     * Export samples
     */
    public function export(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'evaluation_status' => $request->get('evaluation_status'),
            'batch_status' => $request->get('batch_status'),
            'seller_id' => $request->get('seller_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search')
        ];

        $samples = $this->sampleService->getAllSamples(array_merge($filters, ['per_page' => 1000]));
        
        $filename = 'samples_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function () use ($samples) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Sample ID', 'Sample Name', 'Seller', 'Tea Estate', 'Batch Group', 'Batch ID',
                'Number of Samples', 'Weight Per Sample (kg)', 'Total Weight (kg)', 
                'Arrival Date', 'Status', 'Evaluation Status',
                'Aroma Score', 'Liquor Score', 'Appearance Score', 'Overall Score',
                'Evaluation Comments', 'Received By', 'Evaluated By', 'Created At'
            ]);
            
            foreach ($samples as $sample) {
                fputcsv($file, [
                    $sample->sample_id,
                    $sample->sample_name,
                    $sample->seller ? $sample->seller->seller_name : '',
                    $sample->seller ? $sample->seller->tea_estate_name : '',
                    $sample->batchGroup ? $sample->batchGroup->batch_number : 'Not Batched',
                    $sample->batch_id ?? 'Not Assigned',
                    $sample->number_of_samples ?? 1,
                    $sample->weight_per_sample,
                    $sample->sample_weight,
                    $sample->arrival_date ? $sample->arrival_date->format('Y-m-d') : '',
                    $sample->status_label,
                    $sample->evaluation_status_label,
                    $sample->aroma_score,
                    $sample->liquor_score,
                    $sample->appearance_score,
                    $sample->overall_score,
                    $sample->evaluation_comments,
                    $sample->receivedBy ? $sample->receivedBy->name : '',
                    $sample->evaluatedBy ? $sample->evaluatedBy->name : '',
                    $sample->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Validate store request
     */
    protected function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'sample_name' => 'required|string|max:255',
            'seller_id' => 'required|exists:sellers,id',
            'number_of_samples' => 'required|integer|min:1|max:1000',
            'weight_per_sample' => 'nullable|numeric|min:0|max:999.99',
            'arrival_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);
    }

    /**
     * Validate update request
     */
    protected function validateUpdate(Request $request, int $id)
    {
        return Validator::make($request->all(), [
            'sample_name' => 'required|string|max:255',
            'seller_id' => 'required|exists:sellers,id',
            'number_of_samples' => 'required|integer|min:1|max:1000',
            'weight_per_sample' => 'nullable|numeric|min:0|max:999.99',
            'arrival_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);
    }

    /**
     * Validate evaluation request
     */
    protected function validateEvaluation(Request $request)
    {
        return Validator::make($request->all(), [
            'aroma_score' => 'required|numeric|min:0|max:10',
            'liquor_score' => 'required|numeric|min:0|max:10',
            'appearance_score' => 'required|numeric|min:0|max:10',
            'evaluation_comments' => 'nullable|string|max:1000'
        ]);
    }
/**
 * Add approved and evaluated sample to Sales Register
 */
public function addToSalesRegister(int $id, Request $request)
{
    try {
        // Validate the request
        $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'rate_per_kg' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:1000'
        ]);

        $data = $request->all();
        
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found');
        }

        // Check if sample is approved and evaluated
        if ($sample->status !== 'approved' || $sample->evaluation_status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Only approved and evaluated samples can be added to Sales Register');
        }

        // Check if sample already exists in Sales Register
        $existingSalesEntry = SalesRegister::where('sample_id', $sample->id)->first();
        if ($existingSalesEntry) {
            return redirect()->back()
                ->with('error', 'This sample is already added to Sales Register with Entry ID: ' . $existingSalesEntry->sales_entry_id);
        }

        // Validate required fields
        if (empty($data['buyer_id'])) {
            return redirect()->back()
                ->with('error', 'Buyer selection is required');
        }

        // Validate buyer exists
        $buyer = \App\Models\Buyer::find($data['buyer_id']);
        if (!$buyer) {
            return redirect()->back()
                ->with('error', 'Selected buyer not found');
        }

        DB::beginTransaction();

        // Generate unique sales entry ID
        $salesEntryId = SalesRegister::generateSalesEntryId();

        // Calculate total amount if rate is provided
        $totalAmount = 0;
        if (!empty($data['rate_per_kg']) && $sample->sample_weight) {
            $totalAmount = $data['rate_per_kg'] * $sample->sample_weight;
        }

        // Create Sales Register entry directly
        $salesEntry = SalesRegister::create([
            'sales_entry_id' => $salesEntryId,
            'sample_id' => $sample->id,
            'buyer_id' => $data['buyer_id'],
            'product_name' => $sample->sample_name,
            'tea_grade' => $sample->tea_grade ?? 'Not Specified',
            'quantity_kg' => $sample->sample_weight ?? 0,
            'rate_per_kg' => $data['rate_per_kg'] ?? 0,
            'total_amount' => $totalAmount,
            'entry_date' => now()->format('Y-m-d'),
            'status' => SalesRegister::STATUS_PENDING,
            'remarks' => $data['remarks'] ?? 'Added from approved sample: ' . $sample->sample_id . ' (Score: ' . $sample->overall_score . '/10)',
            'created_by' => Auth::id()
        ]);

        DB::commit();

        return redirect()->route('admin.sales-register.show', $salesEntry->id)
            ->with('success', 'Sample successfully added to Sales Register! Entry ID: ' . $salesEntry->sales_entry_id);

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error adding to Sales Register: ' . $e->getMessage());
    }
}

public function showTransferForm(int $id)
{
    try {
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found');
        }

        // Check if sample can be transferred
        if (!$sample->batch_group_id) {
            return redirect()->back()
                ->with('error', 'Sample must be batched before it can be transferred');
        }

        if ($sample->evaluation_status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Only evaluated samples can be transferred for retesting');
        }

        return view('admin.samples.transfer', compact('sample'));

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error loading transfer form: ' . $e->getMessage());
    }
}

/**
 * Process sample transfer to another batch
 */
public function transferToBatch(int $id, Request $request, SampleTransferService $transferService)
{
    try {
        // Validate the request - no weight/quantity input needed
        $request->validate([
            'transfer_reason' => 'required|in:retesting,quality_check,additional_evaluation,other',
            'transfer_remarks' => 'nullable|string|max:1000'
        ]);

        $transferData = $request->only([
            'transfer_reason',
            'transfer_remarks'
        ]);

        // Use new method for fixed allocation transfer
        $result = $transferService->transferSampleForRetesting($id, $transferData);

        return redirect()->route('admin.samples.show', $id)
            ->with('success', $result['message'] . ' New Sample ID: ' . $result['new_sample']->sample_id . ' (10gm allocated)');

    } catch (\Exception $e) {
        return redirect()->back()
            ->withErrors(['error' => $e->getMessage()])
            ->withInput();
    }
}

/**
 * Show sample transfer history
 */
public function transferHistory(int $id, SampleTransferService $transferService)
{
    try {
        $history = $transferService->getSampleTransferHistory($id);
        
        return view('admin.samples.transfer-history', compact('history'));

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error loading transfer history: ' . $e->getMessage());
    }
}

/**
 * List all sample transfers
 */
public function transfers(Request $request, SampleTransferService $transferService)
{
    $filters = [
        'status' => $request->get('status'),
        'transfer_reason' => $request->get('transfer_reason'),
        'start_date' => $request->get('start_date'),
        'end_date' => $request->get('end_date'),
        'search' => $request->get('search'),
        'per_page' => $request->get('per_page', 15)
    ];

    $transfers = $transferService->getAllTransfers($filters);

    return view('admin.samples.transfers', compact('transfers', 'filters'));
}

public function showAllocations(int $id)
{
    try {
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found');
        }

        $allocations = SampleAllocation::where('sample_id', $id)
            ->with(['batchGroup', 'allocatedBy'])
            ->orderBy('allocation_date', 'desc')
            ->paginate(15);

        $allocationSummary = [
            'total_allocations' => $sample->allocation_count,
            'total_allocated_weight' => $sample->allocated_weight,
            'available_weight' => $sample->available_weight,
            'catalog_weight' => $sample->catalog_weight,
            'can_allocate_more' => $sample->has_sufficient_weight,
            'active_allocations' => $sample->activeAllocations()->count(),
            'used_allocations' => $sample->allocations()->where('status', SampleAllocation::STATUS_USED)->count(),
            'returned_allocations' => $sample->allocations()->where('status', SampleAllocation::STATUS_RETURNED)->count()
        ];

        return view('admin.samples.allocations', compact('sample', 'allocations', 'allocationSummary'));

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error loading allocations: ' . $e->getMessage());
    }
}

/**
 * Return an allocation (if not used)
 */
public function returnAllocation(int $sampleId, int $allocationId)
{
    try {
        $sample = Sample::findOrFail($sampleId);
        $allocation = SampleAllocation::findOrFail($allocationId);

        if ($allocation->sample_id !== $sample->id) {
            return redirect()->back()
                ->with('error', 'Allocation does not belong to this sample');
        }

        $sample->returnAllocation($allocation);

        return redirect()->back()
            ->with('success', 'Allocation returned successfully. Weight returned to sample catalog.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error returning allocation: ' . $e->getMessage());
    }
}

/**
 * Mark allocation as used
 */
public function markAllocationUsed(int $sampleId, int $allocationId)
{
    try {
        $sample = Sample::findOrFail($sampleId);
        $allocation = SampleAllocation::findOrFail($allocationId);

        if ($allocation->sample_id !== $sample->id) {
            return redirect()->back()
                ->with('error', 'Allocation does not belong to this sample');
        }

        $sample->markAllocationAsUsed($allocation);

        return redirect()->back()
            ->with('success', 'Allocation marked as used successfully.');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error updating allocation: ' . $e->getMessage());
    }
}

public function showBatchCreation()
{
    $today = Carbon::today();
    
    // Get samples available for batching (with sufficient weight)
    $availableSamples = Sample::unbatched()
        ->forDate($today)
        ->where('has_sufficient_weight', true)
        ->with(['seller'])
        ->get();

    $statistics = [
        'total_available' => $availableSamples->count(),
        'total_catalog_weight' => $availableSamples->sum('catalog_weight'),
        'total_available_weight' => $availableSamples->sum('available_weight'),
        'potential_batches' => ceil($availableSamples->count() / 48),
        'total_allocation_needed' => $availableSamples->count() * Sample::FIXED_ALLOCATION_WEIGHT,
        'samples_insufficient_weight' => Sample::unbatched()
            ->forDate($today)
            ->where('has_sufficient_weight', false)
            ->count()
    ];

    return view('admin.samples.batch-creation', compact('availableSamples', 'statistics', 'today'));
}

/**
 * Get sample catalog summary
 */
public function getCatalogSummary()
{
    $summary = [
        'total_samples' => Sample::count(),
        'total_catalog_weight' => Sample::sum('catalog_weight'),
        'total_allocated_weight' => Sample::sum('allocated_weight'),
        'total_available_weight' => Sample::sum('available_weight'),
        'samples_with_sufficient_weight' => Sample::where('has_sufficient_weight', true)->count(),
        'samples_insufficient_weight' => Sample::where('has_sufficient_weight', false)->count(),
        'total_allocations' => SampleAllocation::count(),
        'active_allocations' => SampleAllocation::where('status', SampleAllocation::STATUS_ALLOCATED)->count(),
        'used_allocations' => SampleAllocation::where('status', SampleAllocation::STATUS_USED)->count(),
        'average_allocation_per_sample' => Sample::avg('allocation_count')
    ];

    return response()->json($summary);
}
}