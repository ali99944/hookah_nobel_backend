<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardMetrics()
    {
        $orders_count = Order::query()->count();
        $sales_revenue = Order::query()->where('status',  'delivered')->sum('total');
        $categories_count = Category::query()->count();
        $products_count = Product::query()->count();

        $revenue_per_day = Order::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('created_at')
            ->get()
            ->pluck('revenue')
            ->toArray();

        $orders_by_status = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->toArray();

        $products_by_category = Product::query()
            ->selectRaw('categories.name as category, COUNT(*) as count')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('category')
            ->get()
            ->toArray();

        return response()->json([
            'orders_count' => $orders_count,
            'sales_revenue' => $sales_revenue,
            'categories_count' => $categories_count,
            'products_count' => $products_count,
            'revenue_per_day' => $revenue_per_day,
            'orders_by_status' => $orders_by_status,
            'products_by_category' => $products_by_category,
        ]);
    }
}
