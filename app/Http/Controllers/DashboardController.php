<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardMetrics()
    {
        $orders_count = Order::query()->count();
        $sales_revenue = Order::query()->where('status',  '')->sum('total_price');

        return response()->json([
            'orders_count' => $orders_count
        ]);
    }
}
