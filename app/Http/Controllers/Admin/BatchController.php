<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchEvaluation;
use App\Models\BatchTesterEvaluation;
use App\Models\Poc;
use App\Services\BatchService;
use App\Models\SampleBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function __construct(
        protected BatchService $batchService
    ) {}

    /**
     * Display batch management page
     */
    public function index(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15)
        ];

        $batches = $this->batchService->getAllBatches($filters);
        $overview = $this->batchService->getBatchOverview();

        return view('admin.batches.index', compact('batches', 'overview', 'filters'));
    }

    /**
     * Show batch details
     */
    public function show(int $id)
    {
        try {
            $batch = $this->batchService->getBatchDetails($id);
            return view('admin.batches.show', compact('batch'));
        } catch (\Exception $e) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Batch not found: ' . $e->getMessage());
        }
    }

    /**
     * Create batches for a specific date
     */
    public function createBatchesForDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date provided',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $date = Carbon::parse($request->date);
            $result = $this->batchService->createBatchesForDate($date, Auth::id());

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$result['batches_created']} batches for {$result['samples_processed']} samples",
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch statistics for a specific date
     */
    public function getDateStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date provided',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $date = Carbon::parse($request->date);
            $statistics = $this->batchService->getBatchStatisticsForDate($date);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a batch and unbatch its samples
     */
    public function destroy(int $id)
    {
        try {
            $this->batchService->deleteBatch($id, Auth::id());
            
            return redirect()->route('admin.batches.index')
                ->with('success', 'Batch deleted successfully. All samples have been unbatched.');
        
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting batch: ' . $e->getMessage());
        }
    }

    /**
     * Rebuild batches for a specific date
     */
    public function rebuildBatchesForDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date provided',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $date = Carbon::parse($request->date);
            
            if (!confirm("Are you sure you want to rebuild all batches for {$date->format('Y-m-d')}? This will delete existing batches and create new ones.")) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operation cancelled'
                ]);
            }

            $result = $this->batchService->rebuildBatchesForDate($date, Auth::id());

            return response()->json([
                'success' => true,
                'message' => "Successfully rebuilt batches. Created {$result['batches_created']} new batches for {$result['samples_processed']} samples",
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export batch data
     */
    public function export(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
            'per_page' => 1000
        ];

        $batches = $this->batchService->getAllBatches($filters);
        
        $filename = 'batches_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function () use ($batches) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Batch Number', 'Batch Date', 'Sequence', 'Total Samples', 
                'Max Samples', 'Status', 'Capacity %', 'Created By', 'Created At'
            ]);
            
            foreach ($batches as $batch) {
                fputcsv($file, [
                    $batch->batch_number,
                    $batch->batch_date->format('Y-m-d'),
                    $batch->batch_sequence,
                    $batch->total_samples,
                    $batch->max_samples,
                    $batch->status_label,
                    number_format($batch->capacity_percentage, 1) . '%',
                    $batch->createdBy ? $batch->createdBy->name : '',
                    $batch->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get batch overview data for dashboard
     */
    public function getOverviewData()
    {
        try {
            $overview = $this->batchService->getBatchOverview();
            
            return response()->json([
                'success' => true,
                'data' => $overview
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,full,processing,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $batch = SampleBatch::findOrFail($id);
            $batch->update([
                'status' => $request->status,
                'updated_by' => Auth::id(),
                'completed_at' => $request->status === 'completed' ? now() : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch status updated successfully',
                'data' => [
                    'status' => $batch->status,
                    'status_label' => $batch->status_label,
                    'status_badge_class' => $batch->status_badge_class
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Show batch evaluation form
     */
    public function showEvaluationForm(int $id)
    {
        try {
            $batch = SampleBatch::with(['samples'])->findOrFail($id);
            
            // Check if batch has samples
            if ($batch->total_samples == 0) {
                return redirect()->route('admin.batches.index')
                    ->with('error', 'This batch has no samples to evaluate.');
            }

            // Get existing evaluation if any
            $evaluation = BatchEvaluation::where('batch_group_id', $id)->first();
            
            // Get testers from POCs where poc_type is 'tester'
            $testers = Poc::where('poc_type', 'tester')
                         ->where('status', true)
                         ->orderBy('poc_name')
                         ->get();
            
            return view('admin.batches.evaluation', compact('batch', 'evaluation', 'testers'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Batch not found: ' . $e->getMessage());
        }
    }

    /**
     * Store batch evaluation
     */
    public function storeEvaluation(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'testers' => 'required|array|min:1',
            'testers.*.tester_poc_id' => 'required|exists:pocs,id',
            'testers.*.c_score' => 'required|integer|min:0|max:100',
            'testers.*.t_score' => 'required|integer|min:0|max:100',
            'testers.*.s_score' => 'required|integer|min:0|max:100',
            'testers.*.b_score' => 'required|integer|min:0|max:100',
            'testers.*.total_samples' => 'required|integer|min:1',
            'testers.*.color_shade' => 'nullable|string',
            'testers.*.brand' => 'nullable|string',
            'testers.*.remarks' => 'nullable|string',
            'overall_remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $batch = SampleBatch::findOrFail($id);
            
            // Create or update batch evaluation
            $evaluation = BatchEvaluation::updateOrCreate(
                ['batch_group_id' => $id],
                [
                    'batch_id' => $batch->batch_number,
                    'evaluation_date' => now()->toDateString(),
                    'total_samples' => $batch->total_samples,
                    'evaluation_status' => BatchEvaluation::STATUS_IN_PROGRESS,
                    'overall_remarks' => $request->overall_remarks,
                    'evaluation_started_by' => Auth::id(),
                    'evaluation_started_at' => now(),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id()
                ]
            );

            // Delete existing tester evaluations
            BatchTesterEvaluation::where('batch_evaluation_id', $evaluation->id)->delete();

            // Create new tester evaluations
            foreach ($request->testers as $testerData) {
                $tester = Poc::findOrFail($testerData['tester_poc_id']);
                
                BatchTesterEvaluation::create([
                    'batch_evaluation_id' => $evaluation->id,
                    'tester_poc_id' => $tester->id,
                    'tester_name' => $tester->poc_name,
                    'c_score' => $testerData['c_score'],
                    't_score' => $testerData['t_score'],
                    's_score' => $testerData['s_score'],
                    'b_score' => $testerData['b_score'],
                    'total_samples' => $testerData['total_samples'],
                    'color_shade' => $testerData['color_shade'] ?? 'RED',
                    'brand' => $testerData['brand'] ?? 'WB',
                    'remarks' => $testerData['remarks'],
                    'evaluation_status' => BatchTesterEvaluation::STATUS_COMPLETED,
                    'evaluated_at' => now(),
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id()
                ]);
            }

            // Update evaluation status to completed
            $evaluation->update([
                'evaluation_status' => BatchEvaluation::STATUS_COMPLETED,
                'evaluation_completed_by' => Auth::id(),
                'evaluation_completed_at' => now(),
                'updated_by' => Auth::id()
            ]);

            // Update batch status based on evaluation results
            $this->updateBatchStatusFromEvaluation($batch, $evaluation);

            DB::commit();

            return redirect()->route('admin.batches.show', $id)
                ->with('success', 'Batch evaluation completed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error saving evaluation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show batch evaluation results
     */
    public function showEvaluationResults(int $id)
    {
        try {
            $batch = SampleBatch::findOrFail($id);
            $evaluation = BatchEvaluation::with(['testerEvaluations.testerPoc'])
                                       ->where('batch_group_id', $id)
                                       ->firstOrFail();
            
            return view('admin.batches.evaluation-results', compact('batch', 'evaluation'));
            
        } catch (\Exception $e) {
            return redirect()->route('admin.batches.index')
                ->with('error', 'Batch evaluation not found: ' . $e->getMessage());
        }
    }

    /**
     * Update batch status based on evaluation results
     */
    private function updateBatchStatusFromEvaluation(SampleBatch $batch, BatchEvaluation $evaluation)
    {
        $averageScores = $evaluation->average_scores;
        $totalScore = $averageScores['c_score'] + $averageScores['t_score'] + 
                     $averageScores['s_score'] + $averageScores['b_score'];
        
        // Update batch status based on evaluation result
        if ($totalScore >= 300) {
            $batch->update(['status' => 'completed']);
        } else {
            $batch->update(['status' => 'processing']);
        }
    }
}