<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'products' => Product::count(),
                'active_products' => Product::where('is_active', true)->count(),
                'materials' => Material::count(),
                'low_stock_materials' => Material::whereColumn('stock', '<=', 'minimum_stock')->count(),
                'orders' => Order::count(),
                'pending_orders' => Order::where('order_status', 'pending')->count(),
                'paid_orders' => Order::where('payment_status', 'paid')->count(),
                'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            ],
        ]);
    }
}
