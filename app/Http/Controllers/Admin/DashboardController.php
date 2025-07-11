<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\{SellerService, BuyerService, CourierService};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $sellerService;
    protected $buyerService;
    protected $courierService;

    public function __construct(
        SellerService $sellerService,
        BuyerService $buyerService,
        CourierService $courierService
    ) {
        // $this->middleware('auth:admin');
        $this->sellerService = $sellerService;
        $this->buyerService = $buyerService;
        $this->courierService = $courierService;
    }

    public function index(Request $request)
    {
        // Get statistics from all services
        $sellerStats = $this->sellerService->getStatistics();
        $buyerStats = $this->buyerService->getStatistics();
        $courierStats = $this->courierService->getStatistics();

        // Recent activities (you can expand this later)
        $recentSellers = $this->sellerService->index(['per_page' => 5]);
        $recentBuyers = $this->buyerService->index(['per_page' => 5]);

        // Calculate some KPIs
        $totalActiveEntities = $sellerStats['active'] + $buyerStats['active'] + $courierStats['active'];
        $totalEntities = $sellerStats['total'] + $buyerStats['total'] + $courierStats['total'];
        $activePercentage = $totalEntities > 0 ? round(($totalActiveEntities / $totalEntities) * 100, 1) : 0;

        // Chart data for tea grades distribution (you can enhance this later)
        $teaGradesData = $this->getTeaGradesDistribution();

        if ($request->ajax()) {
            return response()->json([
                'seller_stats' => $sellerStats,
                'buyer_stats' => $buyerStats,
                'courier_stats' => $courierStats,
                'active_percentage' => $activePercentage,
                'tea_grades_data' => $teaGradesData
            ]);
        }

        return view('admin.dashboard', compact(
            'sellerStats',
            'buyerStats',
            'courierStats',
            'recentSellers',
            'recentBuyers',
            'activePercentage',
            'teaGradesData'
        ));
    }

    protected function getTeaGradesDistribution()
    {
        // This is a placeholder - you would implement actual tea grades statistics
        return [
            'BP' => 15,
            'BOP' => 25,
            'PD' => 12,
            'Dust' => 8,
            'FTGFOP' => 10,
            'Other' => 30
        ];
    }

    public function getQuickStats(Request $request)
    {
        $stats = [
            'sellers' => $this->sellerService->getStatistics(),
            'buyers' => $this->buyerService->getStatistics(),
            'couriers' => $this->courierService->getStatistics(),
        ];

        return response()->json($stats);
    }
}