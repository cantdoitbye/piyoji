<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BatchService;
use App\Models\SampleBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
}