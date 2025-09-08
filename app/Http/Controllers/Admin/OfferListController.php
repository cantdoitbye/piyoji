<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OfferListService;
use App\Services\GardenService;
use App\Models\OfferList;
use App\Imports\OfferListImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class OfferListController extends Controller
{
    public function __construct(
        protected OfferListService $service,
        protected GardenService $gardenService
    ) {}

    /**
     * Display a listing of offer lists
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'garden_id' => $request->get('garden_id'),
            'grade' => $request->get('grade'),
            'type' => $request->get('type'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'per_page' => $request->get('per_page', 15)
        ];

        $offerLists = $this->service->index($filters);
        $gardens = $this->gardenService->getForSelect();
        $statistics = $this->service->getStatistics();

        // Get unique grades and types for filters
        $grades = OfferList::distinct('grade')->whereNotNull('grade')->pluck('grade');
        $types = OfferList::getTypeOptions();

        return view('admin.offer-lists.index', compact(
            'offerLists', 
            'gardens', 
            'statistics', 
            'grades', 
            'types', 
            'filters'
        ));
    }

    /**
     * Show the form for creating a new offer list
     */
    public function create()
    {
        $gardens = $this->gardenService->getForSelect();
        $invPretxOptions = OfferList::getInvPretxOptions();
        $forOptions = OfferList::getForOptions();
        $typeOptions = OfferList::getTypeOptions();

        return view('admin.offer-lists.create', compact(
            'gardens', 
            'invPretxOptions', 
            'forOptions', 
            'typeOptions'
        ));
    }

    /**
     * Store a newly created offer list
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
            $offerList = $this->service->store($validator->validated());

            return redirect()->route('admin.offer-lists.show', $offerList->id)
                ->with('success', 'Offer list created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified offer list
     */
    public function show(int $id)
    {
        $offerList = $this->service->find($id);

        if (!$offerList) {
            return redirect()->route('admin.offer-lists.index')
                ->with('error', 'Offer list not found.');
        }

        return view('admin.offer-lists.show', compact('offerList'));
    }

    /**
     * Show the form for editing the specified offer list
     */
    public function edit(int $id)
    {
        $offerList = $this->service->find($id);

        if (!$offerList) {
            return redirect()->route('admin.offer-lists.index')
                ->with('error', 'Offer list not found.');
        }

        $gardens = $this->gardenService->getForSelect();
        $invPretxOptions = OfferList::getInvPretxOptions();
        $forOptions = OfferList::getForOptions();
        $typeOptions = OfferList::getTypeOptions();

        return view('admin.offer-lists.edit', compact(
            'offerList',
            'gardens', 
            'invPretxOptions', 
            'forOptions', 
            'typeOptions'
        ));
    }

    /**
     * Update the specified offer list
     */
    public function update(Request $request, int $id)
    {
        $validator = $this->validateStore($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->service->update($id, $validator->validated());

            return redirect()->route('admin.offer-lists.show', $id)
                ->with('success', 'Offer list updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified offer list
     */
    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);

            return redirect()->route('admin.offer-lists.index')
                ->with('success', 'Offer list deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export offer lists to CSV
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'garden_id', 'grade', 'type', 'date_from', 'date_to']);
        
        $query = $this->service->index(array_merge($filters, ['per_page' => 10000]));
        $data = $query->items();

        $filename = 'offer_lists_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Device ID', 'AWR No', 'Date', 'For', 'Garden Name', 'Grade', 
                'Inv PreTx', 'Inv No', 'Party 1', 'Party 2', 'Party 3', 'Party 4', 'Party 5',
                'Party 6', 'Party 7', 'Party 8', 'Party 9', 'Party 10',
                'Packages', 'Net1', 'Total KGs', 'D/O Packing', 'Type', 'Key', 'Name of Upload'
            ]);
            
            // Add data rows
            foreach ($data as $offerList) {
                fputcsv($file, [
                    $offerList->device_id,
                    $offerList->awr_no,
                    $offerList->date ? $offerList->date->format('Y-m-d') : '',
                    $offerList->for,
                    $offerList->garden_name,
                    $offerList->grade,
                    $offerList->inv_pretx,
                    $offerList->inv_no,
                    $offerList->party_1,
                    $offerList->party_2,
                    $offerList->party_3,
                    $offerList->party_4,
                    $offerList->party_5,
                    $offerList->party_6,
                    $offerList->party_7,
                    $offerList->party_8,
                    $offerList->party_9,
                    $offerList->party_10,
                    $offerList->pkgs,
                    $offerList->net1,
                    $offerList->ttl_kgs,
                    $offerList->d_o_packing ? $offerList->d_o_packing->format('Y-m-d') : '',
                    $offerList->type,
                    $offerList->key,
                    $offerList->name_of_upload
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download sample Excel template
     */
    public function downloadTemplate()
    {
        $filename = 'offer_list_template.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add headers with sample data
            fputcsv($file, [
                'DeviceID', 'N_AWR_NO', 'Date', 'FOR', 'GARDEN', 'GRADE', 
                'INV_PRETX', 'INV_NO', 'PARTY_1', 'PARTY_2', 'PARTY_3', 'PARTY_4', 'PARTY_5',
                'PARTY_6', 'PARTY_7', 'PARTY_8', 'PARTY_9', 'PARTY_10',
                'PKGS', 'NET1', 'TTL_KGS', 'D_O_PACKING', 'TYPE', 'Key', 'NameOfUpload'
            ]);
            
            // Add sample row
            fputcsv($file, [
                'TIRUPATI', '#N/A', '01/01/2025', 'GTPP', 'NARSINGPORE', 'BOPSM',
                'EX', '9999', 'G-AMD', 'MAMA', 'SVT', 'MALPANI', 'J',
                'RAJANI', 'KANUBHAI', 'CH', '', '', '7', '30', '210',
                '15/03/2023', 'BROKENS', 'NARSINGPORE9999', 'Sample Upload'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Validate store/update request
     */
    private function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'device_id' => 'nullable|string|max:255',
            'awr_no' => 'nullable|string|max:255',
            'date' => 'required|date',
            'for' => 'required|in:GTPP,GTFP',
            'garden_name' => 'required|string|max:255',
            'garden_id' => 'nullable|exists:gardens,id',
            'grade' => 'required|string|max:255',
            'inv_pretx' => 'required|in:C,EX,PR',
            'inv_no' => 'nullable|integer',
            'party_1' => 'nullable|string|max:255',
            'party_2' => 'nullable|string|max:255',
            'party_3' => 'nullable|string|max:255',
            'party_4' => 'nullable|string|max:255',
            'party_5' => 'nullable|string|max:255',
            'party_6' => 'nullable|string|max:255',
            'party_7' => 'nullable|string|max:255',
            'party_8' => 'nullable|string|max:255',
            'party_9' => 'nullable|string|max:255',
            'party_10' => 'nullable|string|max:255',
            'pkgs' => 'nullable|numeric',
            'net1' => 'nullable|numeric',
            'ttl_kgs' => 'nullable|numeric',
            'd_o_packing' => 'nullable|date',
            'type' => 'nullable|in:BROKENS,FANNINGS,D',
            'key' => 'nullable|string|max:255',
            'name_of_upload' => 'nullable|string'
        ]);
    }


    /**
     * Show Excel import form
     */
    public function importForm()
    {
        return view('admin.offer-lists.import');
    }

    /**
     * Process Excel import
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120' // 5MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $import = new OfferListImport();
            Excel::import($import, $request->file('file'));

            $result = $this->service->bulkImportFromExcel($import->getData());

            $message = "Import completed. Created: {$result['created']}, Updated: {$result['updated']}";
            if (!empty($result['errors'])) {
                $message .= ", Errors: " . count($result['errors']);
            }

            return redirect()->route('admin.offer-lists.index')
                ->with('success', $message)
                ->with('import_errors', $result['errors']);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}