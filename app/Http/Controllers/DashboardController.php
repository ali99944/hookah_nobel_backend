<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getDashboardMetrics()
    {
        $ordersCount = Order::query()->count();
        $salesRevenue = (float) Order::query()->where('status', 'delivered')->sum('total');
        $categoriesCount = Category::query()->count();
        $productsCount = Product::query()->count();

        $days = collect(range(6, 0))
            ->map(fn ($offset) => Carbon::now()->subDays($offset)->startOfDay());

        $startDate = $days->first()->copy()->startOfDay();
        $endDate = $days->last()->copy()->endOfDay();

        $rawRevenue = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->pluck('revenue', 'date');

        $revenueLabels = $days->map(fn (Carbon $day) => $day->locale('ar')->translatedFormat('D'))->toArray();
        $revenuePerDay = $days
            ->map(function (Carbon $day) use ($rawRevenue) {
                $key = $day->toDateString();
                return round((float) ($rawRevenue[$key] ?? 0), 2);
            })
            ->toArray();

        $ordersByStatusRaw = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $ordersByStatus = [
            'pending' => (int) ($ordersByStatusRaw['pending'] ?? 0),
            'processing' => (int) ($ordersByStatusRaw['processing'] ?? 0),
            'shipped' => (int) ($ordersByStatusRaw['shipped'] ?? 0),
            'delivered' => (int) ($ordersByStatusRaw['delivered'] ?? 0),
            'cancelled' => (int) ($ordersByStatusRaw['cancelled'] ?? 0),
            'paid' => (int) Order::query()->where('is_paid', true)->count(),
        ];

        $productsByCategory = Product::query()
            ->selectRaw('categories.name as category, COUNT(*) as count')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->pluck('count', 'category')
            ->toArray();

        return response()->json([
            'orders_count' => (int) $ordersCount,
            'sales_revenue' => round($salesRevenue, 2),
            'categories_count' => (int) $categoriesCount,
            'products_count' => (int) $productsCount,
            'revenue_labels' => $revenueLabels,
            'revenue_per_day' => $revenuePerDay,
            'order_by_status' => $ordersByStatus,
            'products_by_category' => $productsByCategory,
        ]);
    }
}
