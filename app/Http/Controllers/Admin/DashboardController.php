<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $stores = Store::where(function($q) {
            $q->where('is_test', false)->orWhereNull('is_test');
        })->get();

        $recentOrders = collect();
        if ($store) {
            $recentOrders = (clone $orderQuery)->with('user')->latest()->take(10)->get();
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalCustomers',
            'totalProducts',
            'stores',
            'recentOrders',
            'store',
            'todayOrders',
            'todayRevenue',
            'completedOrders'
        ));
    }

    private function buildStoreOrderStats(Request $request)
    {
        $orderQuery = DB::table('orders')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->whereNull('orders.deleted_at')
            ->whereNull('stores.deleted_at')
            ->where(function ($q) {
                $q->where('stores.is_test', false)->orWhereNull('stores.is_test');
            })
            ->select(
                'orders.store_id',
                'stores.store_name',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('done', 'completed', 'delivered', 'picked_up') THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('ready', 'ready_for_pickup', 'delivered_store') THEN 1 ELSE 0 END) as ready_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('processing', 'printing', 'in_production', 'shipped') THEN 1 ELSE 0 END) as printing_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) NOT IN ('done', 'completed', 'delivered', 'picked_up', 'ready', 'ready_for_pickup', 'delivered_store', 'processing', 'printing', 'in_production', 'shipped', 'cancelled', 'refunded') OR LOWER(orders.status) IN ('new', 'pending', 'confirmed') THEN 1 ELSE 0 END) as new_orders"),
                DB::raw("SUM(CASE WHEN orders.payment_gateway != 'cash' AND orders.payment_gateway IS NOT NULL THEN orders.total ELSE 0 END) as online_amount"),
                DB::raw("SUM(CASE WHEN orders.payment_gateway = 'cash' THEN orders.total ELSE 0 END) as cash_amount"),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'pending' THEN 1 ELSE 0 END) as pending_orders"),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'pending' THEN orders.total ELSE 0 END) as pending_amount"),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders"),
                DB::raw("SUM(CASE WHEN orders.payment_status = 'paid' THEN orders.total ELSE 0 END) as paid_amount")
            )
            ->groupBy('orders.store_id', 'stores.store_name');

        $this->applyDateFilter($orderQuery, $request, 'orders.created_at');

        if ($request->filled('store_id')) {
            $orderQuery->where('orders.store_id', $request->store_id);
        }

        $aggregated = $orderQuery->get()->keyBy('store_id');

        // Get all stores (or filtered) so stores with 0 orders still appear (excluding test stores)
        $storesQuery = Store::query();
        if ($request->filled('store_id')) {
            $storesQuery->where('id', $request->store_id);
        } else {
            $storesQuery->where(function ($q) {
                $q->where('is_test', false)->orWhereNull('is_test');
            });
        }

        $rows = $storesQuery->get()->map(function ($store) use ($aggregated) {
            $agg = $aggregated[$store->id] ?? null;
            return [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'total_orders' => (int) ($agg->total_orders ?? 0),
                'new_orders' => (int) ($agg->new_orders ?? 0),
                'printing_orders' => (int) ($agg->printing_orders ?? 0),
                'ready_orders' => (int) ($agg->ready_orders ?? 0),
                'picked_up_orders' => (int) ($agg->picked_up_orders ?? 0),
                'online_amount' => round((float) ($agg->online_amount ?? 0), 2),
                'cash_amount' => round((float) ($agg->cash_amount ?? 0), 2),
                'pending_orders' => (int) ($agg->pending_orders ?? 0),
                'pending_amount' => round((float) ($agg->pending_amount ?? 0), 2),
                'paid_orders' => (int) ($agg->paid_orders ?? 0),
                'paid_amount' => round((float) ($agg->paid_amount ?? 0), 2),
            ];
        });

        $totals = [
            'total_orders' => $rows->sum('total_orders'),
            'new_orders' => $rows->sum('new_orders'),
            'printing_orders' => $rows->sum('printing_orders'),
            'ready_orders' => $rows->sum('ready_orders'),
            'picked_up_orders' => $rows->sum('picked_up_orders'),
            'online_amount' => round($rows->sum('online_amount'), 2),
            'cash_amount' => round($rows->sum('cash_amount'), 2),
            'pending_orders' => $rows->sum('pending_orders'),
            'pending_amount' => round($rows->sum('pending_amount'), 2),
            'paid_orders' => $rows->sum('paid_orders'),
            'paid_amount' => round($rows->sum('paid_amount'), 2),
        ];

        return [$rows, $totals];
    }

    public function storesSummary(Request $request)
    {
        [$rows, $totals] = $this->buildStoreOrderStats($request);

        $sortBy = $request->input('sort_by', 'total_orders');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowed = ['total_orders', 'new_orders', 'printing_orders', 'ready_orders', 'picked_up_orders', 'online_amount', 'cash_amount', 'pending_orders', 'pending_amount', 'paid_orders', 'paid_amount', 'store_name'];
        if (!in_array($sortBy, $allowed)) $sortBy = 'total_orders';

        $rows = $sortDir === 'asc'
            ? $rows->sortBy($sortBy)->values()
            : $rows->sortByDesc($sortBy)->values();

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $items,
            'totals' => $totals,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
        ]);
    }

    public function exportStoresSummary(Request $request)
    {
        [$rows, $totals] = $this->buildStoreOrderStats($request);
        $rows = $rows->sortByDesc('total_orders')->values();

        $filename = 'stores-summary-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $totals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Store Name', 'Total Orders', 'New Orders', 'Printing Orders', 'Ready for Pickup', 'Picked Up', 'Online Payment', 'Cash Payment', 'Pending Orders', 'Pending Amount', 'Paid Orders', 'Paid Amount']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['store_name'], $row['total_orders'], $row['new_orders'], $row['printing_orders'], $row['ready_orders'], $row['picked_up_orders'], $row['online_amount'],
                    $row['cash_amount'], $row['pending_orders'], $row['pending_amount'], $row['paid_orders'], $row['paid_amount'],
                ]);
            }
            fputcsv($handle, [
                'TOTAL', $totals['total_orders'], $totals['new_orders'], $totals['printing_orders'], $totals['ready_orders'], $totals['picked_up_orders'], $totals['online_amount'],
                $totals['cash_amount'], $totals['pending_orders'], $totals['pending_amount'], $totals['paid_orders'], $totals['paid_amount'],
            ]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function applyDateFilter($query, Request $request, string $column = 'created_at')
    {
        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereDate($column, '>=', $request->date_start)
                  ->whereDate($column, '<=', $request->date_end);
        } elseif ($request->filled('preset')) {
            switch ($request->preset) {
                case 'today':
                    $query->whereDate($column, today());
                    break;
                case 'week':
                    $query->where($column, '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where($column, '>=', now()->startOfMonth());
                    break;
                case 'year':
                    $query->where($column, '>=', now()->startOfYear());
                    break;
            }
        }
        return $query;
    }

    private function buildStoreStats(Request $request)
    {
        $storeFilter = $request->input('store_id');

        // Direct store_id tables: products, coupons
        $productCounts = DB::table('products')->whereNull('deleted_at')->select('store_id', DB::raw('COUNT(*) as cnt'));
        $this->applyDateFilter($productCounts, $request);
        if ($storeFilter && $storeFilter !== 'admin') {
            $productCounts->where('store_id', $storeFilter);
        }
        $productCounts = $productCounts->groupBy('store_id')->pluck('cnt', 'store_id');

        $couponCounts = DB::table('coupons')->whereNull('deleted_at')->select('store_id', DB::raw('COUNT(*) as cnt'));
        $this->applyDateFilter($couponCounts, $request);
        if ($storeFilter && $storeFilter !== 'admin') {
            $couponCounts->where('store_id', $storeFilter);
        }
        $couponCounts = $couponCounts->groupBy('store_id')->pluck('cnt', 'store_id');

        // Pivot tables: events (event_store), paper_types (paper_type_store)
        $eventCountsQuery = DB::table('event_store')
            ->join('events', 'events.id', '=', 'event_store.event_id')
            ->whereNull('events.deleted_at')
            ->select('event_store.store_id', DB::raw('COUNT(DISTINCT events.id) as cnt'));
        $this->applyDateFilter($eventCountsQuery, $request, 'events.created_at');
        if ($storeFilter && $storeFilter !== 'admin') {
            $eventCountsQuery->where('event_store.store_id', $storeFilter);
        }
        $eventCounts = $eventCountsQuery->groupBy('event_store.store_id')->pluck('cnt', 'store_id');

        $paperTypeCountsQuery = DB::table('paper_type_store')
            ->join('paper_types', 'paper_types.id', '=', 'paper_type_store.paper_type_id')
            ->whereNull('paper_types.deleted_at')
            ->select('paper_type_store.store_id', DB::raw('COUNT(DISTINCT paper_types.id) as cnt'));
        $this->applyDateFilter($paperTypeCountsQuery, $request, 'paper_types.created_at');
        if ($storeFilter && $storeFilter !== 'admin') {
            $paperTypeCountsQuery->where('paper_type_store.store_id', $storeFilter);
        }
        $paperTypeCounts = $paperTypeCountsQuery->groupBy('paper_type_store.store_id')->pluck('cnt', 'store_id');

        // Global tables (no store_id): categories, templates, product_types
        $categoriesQuery = DB::table('categories')->whereNull('deleted_at');
        $this->applyDateFilter($categoriesQuery, $request);
        $globalCategories = $categoriesQuery->count();

        $templatesQuery = DB::table('templates')->whereNull('deleted_at');
        $this->applyDateFilter($templatesQuery, $request);
        $globalTemplates = $templatesQuery->count();

        $productTypesQuery = DB::table('product_types')->whereNull('deleted_at');
        $this->applyDateFilter($productTypesQuery, $request);
        $globalProductTypes = $productTypesQuery->count();

        // Build rows
        $rows = collect();

        // Admin row (unless filtering by a specific store)
        if (!$storeFilter || $storeFilter === 'admin') {
            $rows->push([
                'id' => 'admin',
                'store_name' => 'Admin (Global)',
                'products' => 0,
                'categories' => $globalCategories,
                'card_types' => $globalProductTypes,
                'templates' => $globalTemplates,
                'coupons' => 0,
                'events' => 0,
                'paper_types' => 0,
            ]);
        }

        if ($storeFilter && $storeFilter !== 'admin') {
            $storesQuery = Store::where('id', $storeFilter);
        } else {
            $storesQuery = Store::where(function ($q) {
                $q->where('is_test', false)->orWhereNull('is_test');
            });
        }

        foreach ($storesQuery->get() as $s) {
            $rows->push([
                'id' => $s->id,
                'store_name' => $s->store_name,
                'products' => $productCounts[$s->id] ?? 0,
                'categories' => 0,
                'card_types' => 0,
                'templates' => 0,
                'coupons' => $couponCounts[$s->id] ?? 0,
                'events' => $eventCounts[$s->id] ?? 0,
                'paper_types' => $paperTypeCounts[$s->id] ?? 0,
            ]);
        }

        // Grand totals
        $totals = [
            'products' => $rows->sum('products'),
            'categories' => $rows->sum('categories'),
            'card_types' => $rows->sum('card_types'),
            'templates' => $rows->sum('templates'),
            'coupons' => $rows->sum('coupons'),
            'events' => $rows->sum('events'),
            'paper_types' => $rows->sum('paper_types'),
        ];

        return [$rows, $totals];
    }

    public function storeStatistics(Request $request)
    {
        [$rows, $totals] = $this->buildStoreStats($request);

        $sortBy = $request->input('sort_by', 'products');
        $sortDir = $request->input('sort_dir', 'desc');
        $rows = $sortDir === 'asc'
            ? $rows->sortBy($sortBy)->values()
            : $rows->sortByDesc($sortBy)->values();

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $items,
            'totals' => $totals,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
        ]);
    }

    public function exportStoreStatistics(Request $request)
    {
        [$rows, $totals] = $this->buildStoreStats($request);
        $rows = $rows->sortByDesc('products')->values();

        $filename = 'store-statistics-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $totals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Store Name', 'Products', 'Categories', 'Card Types/Sizes', 'Templates', 'Coupons', 'Events', 'Paper Types']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['store_name'], $row['products'], $row['categories'],
                    $row['card_types'], $row['templates'], $row['coupons'],
                    $row['events'], $row['paper_types'],
                ]);
            }
            fputcsv($handle, [
                'TOTAL', $totals['products'], $totals['categories'],
                $totals['card_types'], $totals['templates'], $totals['coupons'],
                $totals['events'], $totals['paper_types'],
            ]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
