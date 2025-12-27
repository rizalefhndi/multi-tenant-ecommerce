<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Analytics', [
            'stats' => $this->analyticsService->getDashboardStats(),
            'revenueChart' => $this->analyticsService->getRevenueChart(30),
            'ordersChart' => $this->analyticsService->getOrdersChart(30),
            'orderStatusDistribution' => $this->analyticsService->getOrderStatusDistribution(),
            'topProducts' => $this->analyticsService->getTopProducts(5),
            'recentOrders' => $this->analyticsService->getRecentOrders(5),
            'monthlyRevenue' => $this->analyticsService->getMonthlyRevenue(),
            'salesByDayOfWeek' => $this->analyticsService->getSalesByDayOfWeek(),
        ]);
    }

    /**
     * Get stats via API (for real-time updates)
     */
    public function stats(): array
    {
        return $this->analyticsService->getDashboardStats();
    }

    /**
     * Get revenue chart data
     */
    public function revenueChart(Request $request): array
    {
        $days = $request->input('days', 30);
        return $this->analyticsService->getRevenueChart($days);
    }

    /**
     * Get orders chart data
     */
    public function ordersChart(Request $request): array
    {
        $days = $request->input('days', 30);
        return $this->analyticsService->getOrdersChart($days);
    }

    /**
     * Get top products
     */
    public function topProducts(Request $request): array
    {
        $limit = $request->input('limit', 5);
        return $this->analyticsService->getTopProducts($limit);
    }
}
