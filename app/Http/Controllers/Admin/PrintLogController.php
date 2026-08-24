<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderPrintLog;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintLogController extends Controller
{
    /**
     * View-only listing of QZ Tray print events. Admin sidebar entry.
     *
     * Filters (analytics — top bar):
     *   preset      today | week | month | year | custom | all
     *   date_start  YYYY-MM-DD (only with preset=custom)
     *   date_end    YYYY-MM-DD (only with preset=custom)
     *   store_id    numeric or 'all'
     *
     * Filters (table):
     *   search       — order_number / printer / tray_label
     *   status       — success | failed | retried
     */
    public function index(Request $request)
    {
        // ── Resolve the effective date range from the preset / custom inputs.
        [$preset, $dateStart, $dateEnd] = $this->resolveDateRange($request);

        $storeId = $request->input('store_id');
        if ($storeId === '' || $storeId === null) $storeId = 'all';

        // ── Analytics: 4 stage counts, computed from print logs joined with
        //    orders (so the numbers respect BOTH the store & date filter and
        //    reflect current order state). One grouped query, no N+1.
        $analytics = $this->buildAnalytics($dateStart, $dateEnd, $storeId);

        // ── Table listing: same date/store filter, plus the row-level
        //    search / status filters.
        $query = OrderPrintLog::with(['order:id,order_number', 'store:id,store_name', 'user:id,name,email'])
            ->orderByDesc('printed_at');

        $this->applyScopeFilters($query, $dateStart, $dateEnd, $storeId);

        if ($request->filled('search')) {
            $q = trim((string) $request->search);
            $query->where(function ($w) use ($q) {
                $w->where('order_number', 'like', "%{$q}%")
                    ->orWhere('printer_name', 'like', "%{$q}%")
                    ->orWhere('tray_label', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Small facets for the row-level filter bar.
        $stores   = Store::orderBy('store_name')->get(['id', 'store_name', 'is_test']);
        $printers = OrderPrintLog::query()
            ->select('printer_name')
            ->groupBy('printer_name')
            ->orderBy('printer_name')
            ->pluck('printer_name');

        return view('admin.print-logs.index', [
            'logs'       => $logs,
            'stores'     => $stores,
            'printers'   => $printers,
            'analytics'  => $analytics,
            'preset'     => $preset,
            'dateStart'  => $dateStart?->toDateString(),
            'dateEnd'    => $dateEnd?->toDateString(),
            'storeId'    => (string) $storeId,
        ]);
    }

    /**
     * Read-only detail view for one print event.
     */
    public function show(OrderPrintLog $orderPrintLog)
    {
        $orderPrintLog->load(['order', 'orderItem', 'store', 'user']);
        return view('admin.print-logs.show', ['log' => $orderPrintLog]);
    }

    /**
     * Stream a CSV export of the current filter's dataset (Excel opens .csv
     * natively). Matches the pattern used by DashboardController's exports.
     */
    public function export(Request $request)
    {
        [$preset, $dateStart, $dateEnd] = $this->resolveDateRange($request);
        $storeId = $request->input('store_id', 'all') ?: 'all';

        $query = OrderPrintLog::with(['order:id,order_number', 'store:id,store_name', 'user:id,name,email'])
            ->orderByDesc('printed_at');
        $this->applyScopeFilters($query, $dateStart, $dateEnd, $storeId);

        if ($request->filled('search')) {
            $q = trim((string) $request->search);
            $query->where(function ($w) use ($q) {
                $w->where('order_number', 'like', "%{$q}%")
                    ->orWhere('printer_name', 'like', "%{$q}%")
                    ->orWhere('tray_label', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'print-logs-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // BOM so Excel opens UTF-8 CSVs correctly.
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'Printed At', 'Order #', 'Store', 'Printer', 'Tray', 'Size',
                'Media', 'gsm', 'User Type', 'Copies', 'Printed By',
                'Status', 'Duration (ms)', 'QZ Tray Version', 'Client IP', 'Error',
            ]);
            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $log) {
                    fputcsv($handle, [
                        optional($log->printed_at)->format('Y-m-d H:i:s'),
                        $log->order_number,
                        $log->store?->store_name,
                        $log->printer_name,
                        $log->tray_label,
                        $log->size,
                        $log->media,
                        $log->gsm,
                        $log->user_type ? 'UT' . $log->user_type : null,
                        $log->copies,
                        $log->user?->name,
                        $log->status,
                        $log->duration_ms,
                        $log->qz_tray_version,
                        $log->client_ip,
                        $log->error_message,
                    ]);
                }
            });
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /* ───────────────────── helpers ───────────────────── */

    /**
     * Turn the request's preset/date-range params into a canonical
     * (preset, Carbon $start, Carbon $end) triple. Default = this week
     * (last 7 days), per the product spec. `preset=all` returns nulls.
     */
    private function resolveDateRange(Request $request): array
    {
        $preset    = $request->input('preset');
        $dateStart = $request->input('date_start');
        $dateEnd   = $request->input('date_end');

        // Custom range wins whenever both bounds are provided.
        if ($dateStart && $dateEnd) {
            return ['custom', now()->parse($dateStart)->startOfDay(), now()->parse($dateEnd)->endOfDay()];
        }

        // Empty request → default preset = "this week" (last 7 days).
        if (!$preset) $preset = 'week';

        return match ($preset) {
            'today' => ['today', now()->startOfDay(),          now()->endOfDay()],
            'week'  => ['week',  now()->subDays(7)->startOfDay(), now()->endOfDay()],
            'month' => ['month', now()->startOfMonth(),        now()->endOfDay()],
            'year'  => ['year',  now()->startOfYear(),         now()->endOfDay()],
            'all'   => ['all',   null,                          null],
            default => ['week',  now()->subDays(7)->startOfDay(), now()->endOfDay()],
        };
    }

    /**
     * Apply the shared date-range + store scope to any print-log query.
     */
    private function applyScopeFilters($query, $dateStart, $dateEnd, $storeId): void
    {
        if ($dateStart) $query->where('printed_at', '>=', $dateStart);
        if ($dateEnd)   $query->where('printed_at', '<=', $dateEnd);
        if ($storeId && $storeId !== 'all') {
            $query->where('store_id', (int) $storeId);
        }
    }

    /**
     * Compute the 4 stage counts (new / printing / ready / picked_up) in a
     * single grouped query. Counts DISTINCT orders that have at least one
     * print log in the filtered window, grouped by the order's canonical
     * queue-stage (same status → stage mapping used by Order::queue_stage).
     */
    private function buildAnalytics($dateStart, $dateEnd, $storeId): array
    {
        $q = DB::table('order_print_logs')
            ->join('orders', 'orders.id', '=', 'order_print_logs.order_id')
            ->select([
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('done', 'completed', 'delivered', 'picked_up') THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('ready', 'ready_for_pickup', 'delivered_store') THEN 1 ELSE 0 END) as ready_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('processing', 'printing', 'in_production', 'shipped') THEN 1 ELSE 0 END) as printing_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('new', 'pending', 'confirmed') OR LOWER(orders.status) NOT IN ('done', 'completed', 'delivered', 'picked_up', 'ready', 'ready_for_pickup', 'delivered_store', 'processing', 'printing', 'in_production', 'shipped', 'cancelled', 'refunded') THEN 1 ELSE 0 END) as new_orders"),
                DB::raw('COUNT(*) as total_logs'),
                DB::raw('COUNT(DISTINCT order_print_logs.order_id) as total_orders'),
            ]);

        if ($dateStart) $q->where('order_print_logs.printed_at', '>=', $dateStart);
        if ($dateEnd)   $q->where('order_print_logs.printed_at', '<=', $dateEnd);
        if ($storeId && $storeId !== 'all') {
            $q->where('order_print_logs.store_id', (int) $storeId);
        }

        $row = $q->first();

        return [
            'new_orders'       => (int) ($row->new_orders ?? 0),
            'printing_orders'  => (int) ($row->printing_orders ?? 0),
            'ready_orders'     => (int) ($row->ready_orders ?? 0),
            'picked_up_orders' => (int) ($row->picked_up_orders ?? 0),
            'total_logs'       => (int) ($row->total_logs ?? 0),
            'total_orders'     => (int) ($row->total_orders ?? 0),
        ];
    }
}
