<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $orderQuery = Order::query();
        $userQuery = User::query();
        $productQuery = Product::query();

        // Store Isolation
        $store = null;
        if (auth()->user()->store_id) {
            $orderQuery->where('store_id', auth()->user()->store_id);
            $orderQuery->where('created_at', '>=', now()->subDays(30));
            $store = Store::find(auth()->user()->store_id);
            // $userQuery->where('store_id', auth()->user()->store_id); // Customers are global
            // $productQuery->where('store_id', auth()->user()->store_id); // Products are global
        }

        $totalRevenue = (clone $orderQuery)->where('payment_status', 'paid')->sum('total');
        $totalOrders = (clone $orderQuery)->count();
        $pendingOrders = (clone $orderQuery)->where('status', 'pending')->count();
        $totalCustomers = $userQuery->where('role', 'customer')->count();
        $totalProducts = $productQuery->count();

        // Extra stats for store_admin
        $todayOrders = 0;
        $todayRevenue = 0;
        $completedOrders = 0;
        if ($store) {
            $todayOrders = Order::where('store_id', $store->id)
                ->whereDate('created_at', today())
                ->count();
            $todayRevenue = Order::where('store_id', $store->id)
                ->whereDate('created_at', today())
                ->where('payment_status', 'paid')
                ->sum('total');
            $completedOrders = Order::where('store_id', $store->id)
                ->whereIn('status', ['completed', 'delivered'])
                ->count();
        }

        $recentOrders = $orderQuery->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalCustomers',
            'totalProducts',
            'recentOrders',
            'store',
            'todayOrders',
            'todayRevenue',
            'completedOrders'
        ));
    }
}
