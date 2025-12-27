<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get dashboard overview stats
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        return [
            'revenue' => [
                'today' => $this->getRevenue($today, now()),
                'this_month' => $this->getRevenue($thisMonth, now()),
                'last_month' => $this->getRevenue($lastMonth, $lastMonthEnd),
                'growth' => $this->calculateGrowth(
                    $this->getRevenue($lastMonth, $lastMonthEnd),
                    $this->getRevenue($thisMonth, now())
                ),
            ],
            'orders' => [
                'today' => $this->getOrderCount($today, now()),
                'this_month' => $this->getOrderCount($thisMonth, now()),
                'pending' => Order::where('status', 'pending_payment')->count(),
                'processing' => Order::where('status', 'processing')->count(),
            ],
            'products' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'out_of_stock' => Product::where('stock', '<=', 0)->count(),
                'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            ],
            'customers' => [
                'total' => User::count(),
                'new_this_month' => User::where('created_at', '>=', $thisMonth)->count(),
            ],
        ];
    }

    /**
     * Get revenue for a period
     */
    public function getRevenue(Carbon $start, Carbon $end): float
    {
        return (float) Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'payment_received'])
            ->sum('total') ?? 0;
    }

    /**
     * Get order count for a period
     */
    public function getOrderCount(Carbon $start, Carbon $end): int
    {
        return Order::whereBetween('created_at', [$start, $end])->count();
    }

    /**
     * Get revenue chart data (last 30 days)
     */
    public function getRevenueChart(int $days = 30): array
    {
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = $this->getRevenue($date, $date->copy()->endOfDay());
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get orders chart data (last 30 days)
     */
    public function getOrdersChart(int $days = 30): array
    {
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            $data[] = $this->getOrderCount($date, $date->copy()->endOfDay());
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get order status distribution
     */
    public function getOrderStatusDistribution(): array
    {
        $statuses = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'labels' => array_keys($statuses),
            'data' => array_values($statuses),
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $limit = 5): array
    {
        return Product::select('products.*')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'price' => $product->price,
                    'total_sold' => (int) $product->total_sold,
                    'stock' => $product->stock,
                ];
            })
            ->toArray();
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 5): array
    {
        return Order::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name ?? 'Guest',
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d M Y H:i'),
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly revenue comparison (this year)
     */
    public function getMonthlyRevenue(): array
    {
        $data = [];
        $labels = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create(now()->year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            
            $labels[] = $start->format('M');
            $data[] = $this->getRevenue($start, $end);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get sales by day of week
     */
    public function getSalesByDayOfWeek(): array
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $thisMonth = Carbon::now()->startOfMonth();
        
        $salesByDay = Order::where('created_at', '>=', $thisMonth)
            ->whereIn('status', ['delivered', 'shipped', 'processing', 'payment_received'])
            ->get()
            ->groupBy(function ($order) {
                return $order->created_at->dayOfWeek;
            })
            ->map(function ($orders) {
                return $orders->sum('total');
            });

        $data = [];
        foreach (range(0, 6) as $day) {
            $data[] = $salesByDay[$day] ?? 0;
        }

        return [
            'labels' => $days,
            'data' => $data,
        ];
    }

    /**
     * Calculate growth percentage
     */
    protected function calculateGrowth(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
