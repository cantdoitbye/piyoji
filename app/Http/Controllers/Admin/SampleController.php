<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SampleService;
use App\Services\SellerService;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SamplesImport;

class SampleController extends Controller
{
    public function __construct(
        protected SampleService $sampleService,
        protected SellerService $sellerService
    ) {
        // Middleware is already applied in routes, no need to add here
    }

    /**
     * Display a listing of samples
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'evaluation_status' => $request->get('evaluation_status'),
            'seller_id' => $request->get('seller_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15)
        ];

        $samples = $this->sampleService->getAllSamples($filters);
        $sellers = $this->sellerService->getForSelect();
        $statistics = $this->sampleService->getSampleStatistics();

        return view('admin.samples.index', compact('samples', 'sellers', 'statistics', 'filters'));
    }

    /**
     * Show the form for creating a new sample
     */
    public function create()
    {
        $sellers = $this->sellerService->getForSelect();
        // dd($sellers);
        $teaGrades = $this->sampleService->getAvailableTeaGrades();
        
        return view('admin.samples.create', compact('sellers', 'teaGrades'));
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
            
            return redirect()->route('admin.samples.show', $sample->id)
                ->with('success', 'Sample created successfully.');
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
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found.');
        }

        return view('admin.samples.show', compact('sample'));
    }

    /**
     * Show the form for editing the specified sample
     */
    public function edit(int $id)
    {
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found.');
        }

        $sellers = $this->sellerService->getForSelect();
        $teaGrades = $this->sampleService->getAvailableTeaGrades();
        
        return view('admin.samples.edit', compact('sample', 'sellers', 'teaGrades'));
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
            
            return redirect()->route('admin.samples.show', $sample->id)
                ->with('success', 'Sample updated successfully.');
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
                ->with('success', 'Sample deleted successfully.');
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
        $sample = $this->sampleService->getSampleById($id);
        
        if (!$sample) {
            return redirect()->route('admin.samples.index')
                ->with('error', 'Sample not found.');
        }

        if ($sample->evaluation_status === Sample::EVALUATION_COMPLETED) {
            return redirect()->route('admin.samples.show', $id)
                ->with('info', 'Sample has already been evaluated.');
        }

        return view('admin.samples.evaluate', compact('sample'));
    }

    /**
     * Store sample evaluation
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
            $sample = $this->sampleService->saveEvaluation($id, $validator->validated());
            
            return redirect()->route('admin.samples.show', $sample->id)
                ->with('success', 'Sample evaluation completed successfully.');
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
            $sample = $this->sampleService->startEvaluation($id, Auth::guard('admin')->id());
            
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
     * Show bulk upload form
     */
    public function bulkUpload()
    {
        $sellers = $this->sellerService->getForSelect();
        
        return view('admin.samples.bulk-upload', compact('sellers'));
    }

    /**
     * Process bulk upload
     */
    public function processBulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $import = new SamplesImport();
            Excel::import($import, $request->file('file'));
            
            $result = $this->sampleService->bulkCreateSamples($import->getData(), Auth::guard('admin')->id());
            
            $message = "Bulk upload completed. Created: {$result['created']}, Errors: " . count($result['errors']);
            
            return redirect()->route('admin.samples.index')
                ->with('success', $message)
                ->with('bulk_result', $result);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Export samples
     */
    public function export(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'evaluation_status' => $request->get('evaluation_status'),
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
                'Sample ID', 'Sample Name', 'Seller', 'Tea Estate', 'Batch ID',
                'Weight (kg)', 'Arrival Date', 'Status', 'Evaluation Status',
                'Aroma Score', 'Liquor Score', 'Appearance Score', 'Overall Score',
                'Evaluation Comments', 'Received By', 'Evaluated By', 'Created At'
            ]);
            
            foreach ($samples as $sample) {
                fputcsv($file, [
                    $sample->sample_id,
                    $sample->sample_name,
                    $sample->seller ? $sample->seller->seller_name : '',
                    $sample->seller ? $sample->seller->tea_estate_name : '',
                    $sample->batch_id,
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
     * Search samples for AJAX calls
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $samples = $this->sampleService->searchSamples($query);
            
            return response()->json([
                'success' => true,
                'data' => $samples->map(function ($sample) {
                    return [
                        'id' => $sample->id,
                        'sample_id' => $sample->sample_id,
                        'sample_name' => $sample->sample_name,
                        'seller_name' => $sample->seller->seller_name,
                        'batch_id' => $sample->batch_id,
                        'status' => $sample->status_label
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get samples for select dropdown
     */
    public function getForSelect(Request $request)
    {
        try {
            $status = $request->get('status', 'approved');
            $samples = $this->sampleService->getAllSamples(['status' => $status]);
            
            return response()->json([
                'success' => true,
                'data' => $samples->map(function ($sample) {
                    return [
                        'id' => $sample->id,
                        'text' => $sample->sample_name . ' (' . $sample->sample_id . ')',
                        'sample_id' => $sample->sample_id,
                        'seller_name' => $sample->seller->seller_name
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate store request
     */
    protected function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'sample_name' => 'required|string|max:255',
            'seller_id' => 'required|exists:sellers,id',
            'batch_id' => 'required|string|max:255',
            'sample_weight' => 'nullable|numeric|min:0|max:999.99',
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
            'batch_id' => 'required|string|max:255',
            'sample_weight' => 'nullable|numeric|min:0|max:999.99',
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