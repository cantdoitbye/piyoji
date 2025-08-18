<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SampleService;
use App\Services\SellerService;
use App\Services\BatchService;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SampleController extends Controller
{
    public function __construct(
        protected SampleService $sampleService,
        protected SellerService $sellerService,
        protected BatchService $batchService
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
}