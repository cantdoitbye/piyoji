<?php

namespace App\Services;

use App\Models\SalesRegister;
use App\Models\Buyer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalesRegisterService
{
    /**
     * Get all sales entries with pagination and filters
     */
    public function getAllSalesEntries(array $filters = [])
    {
        $query = SalesRegister::with(['buyer', 'createdBy', 'approvedBy', 'rejectedBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['buyer_id'])) {
            $query->where('buyer_id', $filters['buyer_id']);
        }

        if (!empty($filters['tea_grade'])) {
            $query->where('tea_grade', $filters['tea_grade']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('entry_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('entry_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sales_entry_id', 'like', '%' . $search . '%')
                  ->orWhere('product_name', 'like', '%' . $search . '%')
                  ->orWhere('tea_grade', 'like', '%' . $search . '%')
                  ->orWhereHas('buyer', function ($buyerQuery) use ($search) {
                      $buyerQuery->where('buyer_name', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get sales entry by ID with relationships
     */
    public function getSalesEntryById(int $id): ?SalesRegister
    {
        return SalesRegister::with([
            'buyer',
            'createdBy',
            'updatedBy',
            'approvedBy',
            'rejectedBy'
        ])->find($id);
    }

    /**
     * Create new sales entry
     */
    public function createSalesEntry(array $data): SalesRegister
    {
        try {
            DB::beginTransaction();

            // Validate buyer exists
            $buyer = Buyer::find($data['buyer_id']);
            if (!$buyer) {
                throw new \Exception('Buyer not found');
            }

            // Generate unique sales entry ID
            $salesEntryId = SalesRegister::generateSalesEntryId();

            // Set default values
            $salesData = array_merge($data, [
                'sales_entry_id' => $salesEntryId,
                'status' => SalesRegister::STATUS_PENDING,
                'entry_date' => $data['entry_date'] ?? now()->format('Y-m-d'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);

            $salesEntry = SalesRegister::create($salesData);

            DB::commit();
            return $salesEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update sales entry
     */
    public function updateSalesEntry(int $id, array $data): SalesRegister
    {
        try {
            DB::beginTransaction();

            $salesEntry = SalesRegister::find($id);
            if (!$salesEntry) {
                throw new \Exception('Sales entry not found');
            }

            // Check if entry can be updated
            if ($salesEntry->status === SalesRegister::STATUS_APPROVED) {
                throw new \Exception('Cannot update approved sales entry');
            }

            $data['updated_by'] = Auth::id();
            $salesEntry->update($data);

            DB::commit();
            return $salesEntry->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete sales entry
     */
    public function deleteSalesEntry(int $id): bool
    {
        try {
            DB::beginTransaction();

            $salesEntry = SalesRegister::find($id);
            if (!$salesEntry) {
                throw new \Exception('Sales entry not found');
            }

            // Check if entry can be deleted
            if ($salesEntry->status === SalesRegister::STATUS_APPROVED) {
                throw new \Exception('Cannot delete approved sales entry');
            }

            $salesEntry->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve sales entry
     */
    public function approveSalesEntry(int $id, string $remarks = null): SalesRegister
    {
        try {
            DB::beginTransaction();

            $salesEntry = SalesRegister::find($id);
            if (!$salesEntry) {
                throw new \Exception('Sales entry not found');
            }

            if ($salesEntry->status !== SalesRegister::STATUS_PENDING) {
                throw new \Exception('Only pending entries can be approved');
            }

            $salesEntry->approve(Auth::id(), $remarks);

            DB::commit();
            return $salesEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject sales entry
     */
    public function rejectSalesEntry(int $id, string $reason): SalesRegister
    {
        try {
            DB::beginTransaction();

            $salesEntry = SalesRegister::find($id);
            if (!$salesEntry) {
                throw new \Exception('Sales entry not found');
            }

            if ($salesEntry->status !== SalesRegister::STATUS_PENDING) {
                throw new \Exception('Only pending entries can be rejected');
            }

            $salesEntry->reject(Auth::id(), $reason);

            DB::commit();
            return $salesEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get sales statistics
     */
    public function getSalesStatistics(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total' => SalesRegister::count(),
            'pending' => SalesRegister::pending()->count(),
            'approved' => SalesRegister::approved()->count(),
            'rejected' => SalesRegister::rejected()->count(),
            'today' => SalesRegister::whereDate('entry_date', $today)->count(),
            'this_month' => SalesRegister::where('entry_date', '>=', $thisMonth)->count(),
            'total_approved_amount' => SalesRegister::approved()->sum('total_amount'),
            'pending_amount' => SalesRegister::pending()->sum('total_amount'),
            'today_entries' => SalesRegister::whereDate('created_at', $today)->count()
        ];
    }

    /**
     * Get sales report data
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = SalesRegister::with(['buyer']);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('entry_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('entry_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['buyer_id'])) {
            $query->where('buyer_id', $filters['buyer_id']);
        }

        $salesEntries = $query->orderBy('entry_date', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_entries' => $salesEntries->count(),
            'total_quantity' => $salesEntries->sum('quantity_kg'),
            'total_amount' => $salesEntries->sum('total_amount'),
            'approved_entries' => $salesEntries->where('status', 'approved')->count(),
            'approved_amount' => $salesEntries->where('status', 'approved')->sum('total_amount'),
            'pending_entries' => $salesEntries->where('status', 'pending')->count(),
            'pending_amount' => $salesEntries->where('status', 'pending')->sum('total_amount'),
            'rejected_entries' => $salesEntries->where('status', 'rejected')->count(),
            'rejected_amount' => $salesEntries->where('status', 'rejected')->sum('total_amount')
        ];

        return [
            'sales_entries' => $salesEntries,
            'summary' => $summary,
            'filters' => $filters
        ];
    }

    /**
     * Get buyers for dropdown
     */
    public function getBuyersForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Buyer::active()->select('id', 'buyer_name', 'buyer_type')->orderBy('buyer_name')->get();
    }

    /**
     * Get tea grades for dropdown
     */
    public function getTeaGradesForSelect(): array
    {
        return SalesRegister::TEA_GRADES;
    }

    /**
     * Get recent sales entries for dashboard
     */
    public function getRecentSalesEntries(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return SalesRegister::with(['buyer', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}