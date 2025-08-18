<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesRegisterService;
use App\Models\SalesRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SalesRegisterController extends Controller
{
    public function __construct(
        protected SalesRegisterService $salesRegisterService
    ) {}

    /**
     * Display sales register listing
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'buyer_id' => $request->get('buyer_id'),
            'tea_grade' => $request->get('tea_grade'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 15)
        ];

        $salesEntries = $this->salesRegisterService->getAllSalesEntries($filters);
        $statistics = $this->salesRegisterService->getSalesStatistics();
        $buyers = $this->salesRegisterService->getBuyersForSelect();
        $teaGrades = $this->salesRegisterService->getTeaGradesForSelect();

        return view('admin.sales-register.index', compact(
            'salesEntries', 
            'statistics', 
            'buyers', 
            'teaGrades', 
            'filters'
        ));
    }

    /**
     * Show the form for creating a new sales entry
     */
    public function create()
    {
        $buyers = $this->salesRegisterService->getBuyersForSelect();
        $teaGrades = $this->salesRegisterService->getTeaGradesForSelect();

        return view('admin.sales-register.create', compact('buyers', 'teaGrades'));
    }

    /**
     * Store a newly created sales entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'product_name' => 'required|string|max:255',
            'tea_grade' => 'required|string|max:100',
            'quantity_kg' => 'required|numeric|min:0.01|max:99999.99',
            'rate_per_kg' => 'required|numeric|min:0.01|max:99999.99',
            'entry_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $salesEntry = $this->salesRegisterService->createSalesEntry($request->all());
            
            return redirect()->route('admin.sales-register.index')
                ->with('success', 'Sales entry created successfully! Entry ID: ' . $salesEntry->sales_entry_id);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified sales entry
     */
    public function show(int $id)
    {
        try {
            $salesEntry = $this->salesRegisterService->getSalesEntryById($id);
            
            if (!$salesEntry) {
                return redirect()->route('admin.sales-register.index')
                    ->with('error', 'Sales entry not found');
            }

            return view('admin.sales-register.show', compact('salesEntry'));
        } catch (\Exception $e) {
            return redirect()->route('admin.sales-register.index')
                ->with('error', 'Error loading sales entry: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified sales entry
     */
    public function edit(int $id)
    {
        try {
            $salesEntry = $this->salesRegisterService->getSalesEntryById($id);
            
            if (!$salesEntry) {
                return redirect()->route('admin.sales-register.index')
                    ->with('error', 'Sales entry not found');
            }

            if ($salesEntry->status === SalesRegister::STATUS_APPROVED) {
                return redirect()->route('admin.sales-register.show', $id)
                    ->with('error', 'Cannot edit approved sales entry');
            }

            $buyers = $this->salesRegisterService->getBuyersForSelect();
            $teaGrades = $this->salesRegisterService->getTeaGradesForSelect();
            
            return view('admin.sales-register.edit', compact('salesEntry', 'buyers', 'teaGrades'));
        } catch (\Exception $e) {
            return redirect()->route('admin.sales-register.index')
                ->with('error', 'Error loading sales entry: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified sales entry
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'buyer_id' => 'required|exists:buyers,id',
            'product_name' => 'required|string|max:255',
            'tea_grade' => 'required|string|max:100',
            'quantity_kg' => 'required|numeric|min:0.01|max:99999.99',
            'rate_per_kg' => 'required|numeric|min:0.01|max:99999.99',
            'entry_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $salesEntry = $this->salesRegisterService->updateSalesEntry($id, $request->all());
            
            return redirect()->route('admin.sales-register.show', $id)
                ->with('success', 'Sales entry updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified sales entry
     */
    public function destroy(int $id)
    {
        try {
            $this->salesRegisterService->deleteSalesEntry($id);
            
            return redirect()->route('admin.sales-register.index')
                ->with('success', 'Sales entry deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve sales entry
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $salesEntry = $this->salesRegisterService->approveSalesEntry($id, $request->remarks);
            
            return redirect()->route('admin.sales-register.show', $id)
                ->with('success', 'Sales entry approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Reject sales entry
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $salesEntry = $this->salesRegisterService->rejectSalesEntry($id, $request->rejection_reason);
            
            return redirect()->route('admin.sales-register.show', $id)
                ->with('success', 'Sales entry rejected');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show sales report
     */
    public function report(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'status' => $request->get('status'),
            'buyer_id' => $request->get('buyer_id')
        ];

        $report = $this->salesRegisterService->getSalesReport($filters);
        $buyers = $this->salesRegisterService->getBuyersForSelect();
        
        return view('admin.sales-register.report', compact('report', 'buyers', 'filters'));
    }
}
  