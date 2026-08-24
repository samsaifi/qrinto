<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates the previous week's activity across Orders, Print Logs,
 * Kiosks, Stores, Payments, and daily trends — one call per section,
 * all in database-level aggregates (COUNT / SUM / GROUP BY).
 *
 * Status buckets and payment logic mirror App\Http\Controllers\Admin\
 * DashboardController::buildStoreOrderStats so this report matches the
 * numbers an admin sees in /admin/dashboard and /admin/print-logs.
 */
class WeeklyAnalyticsService
{
    /* Canonical status → stage buckets (copied verbatim from
       DashboardController so the two never drift). */
    private const PICKED_UP = ['done', 'completed', 'delivered', 'picked_up'];
    private const READY     = ['ready', 'ready_for_pickup', 'delivered_store'];
    private const PRINTING  = ['processing', 'printing', 'in_production', 'shipped'];
    private const CANCELLED = ['cancelled', 'refunded'];

    public function __construct(
        private CarbonImmutable $start,
        private CarbonImmutable $end,
    ) {}

    /**
     * Build a service for the previous 7 completed days in the app timezone.
     * Example: called on Aug 21 → Aug 14 00:00 to Aug 20 23:59:59.
     */
    public static function previousWeek(): self
    {
        $tz = config('app.timezone', 'UTC');
        $end = CarbonImmutable::yesterday($tz)->endOfDay();
        $start = $end->subDays(6)->startOfDay();
        return new self($start, $end);
    }

    public static function forRange(string $from, string $until): self
    {
        $tz = config('app.timezone', 'UTC');
        return new self(
            CarbonImmutable::parse($from, $tz)->startOfDay(),
            CarbonImmutable::parse($until, $tz)->endOfDay(),
        );
    }

    public function period(): array
    {
        return [
            'start' => $this->start,
            'end'   => $this->end,
            'label' => $this->start->format('j M Y') . ' – ' . $this->end->format('j M Y'),
            'days'  => $this->start->diffInDays($this->end) + 1,
        ];
    }

    /**
     * Executive summary — high-level counters used at the top of the email.
     * One aggregate query for orders, one for print logs, one for kiosks.
     */
    public function summary(): array
    {
        $orders   = $this->ordersAggregate();
        $prints   = $this->printLogsAggregate();
        $kiosks   = $this->kioskCounts();

        return [
            'total_orders'        => (int) $orders->total_orders,
            'total_revenue'       => (float) $orders->paid_amount,
            'total_pending_amt'   => (float) $orders->pending_amount,
            'total_print_events' => (int) $prints->total_logs,
            'total_picked_up'     => (int) $orders->picked_up_orders,
            'total_pending'       => (int) $orders->pending_orders,
            'total_kiosks'        => (int) $kiosks['total'],
            'active_kiosks'       => (int) $kiosks['active'],
        ];
    }

    /**
     * Order-level breakdown (mirrors DashboardController's totals).
     */
    public function orders(): array
    {
        $r = $this->ordersAggregate();
        return [
            'total_orders'      => (int) $r->total_orders,
            'new_orders'        => (int) $r->new_orders,
            'printing_orders'   => (int) $r->printing_orders,
            'ready_orders'      => (int) $r->ready_orders,
            'picked_up_orders'  => (int) $r->picked_up_orders,
            'cancelled_orders'  => (int) $r->cancelled_orders,
            'pending_orders'    => (int) $r->pending_orders,
            'paid_orders'       => (int) $r->paid_orders,
            'online_amount'     => (float) $r->online_amount,
            'cash_amount'       => (float) $r->cash_amount,
            'pending_amount'    => (float) $r->pending_amount,
            'paid_amount'       => (float) $r->paid_amount,
            'total_revenue'     => (float) $r->paid_amount, // paid == recognised revenue
            'total_value'       => (float) $r->total_value, // gross of all orders
        ];
    }

    /**
     * Print-log stage counts within the reporting window.
     * "Stage" comes from the ORDER's current status — same semantics as
     * PrintLogController@buildAnalytics.
     */
    public function printLogs(): array
    {
        $r = $this->printLogsAggregate();
        return [
            'total_logs'       => (int) $r->total_logs,
            'total_orders'     => (int) $r->distinct_orders,
            'success_count'    => (int) $r->success_count,
            'failed_count'     => (int) $r->failed_count,
            'retried_count'    => (int) $r->retried_count,
            'new_orders'       => (int) $r->new_orders,
            'printing_orders'  => (int) $r->printing_orders,
            'ready_orders'     => (int) $r->ready_orders,
            'picked_up_orders' => (int) $r->picked_up_orders,
        ];
    }

    /**
     * Printer × count breakdown for the week. Top N ordered by activity.
     */
    public function printerBreakdown(int $limit = 20): Collection
    {
        return DB::table('order_print_logs')
            ->select('printer_name', DB::raw('COUNT(*) as events'), DB::raw('COUNT(DISTINCT order_id) as orders'))
            ->whereBetween('printed_at', [$this->start, $this->end])
            ->groupBy('printer_name')
            ->orderByDesc('events')
            ->limit($limit)
            ->get();
    }

    /**
     * Media / paper / size breakdown for the week.
     */
    public function mediaBreakdown(): Collection
    {
        return DB::table('order_print_logs')
            ->select('size', 'media', 'gsm', DB::raw('COUNT(*) as events'))
            ->whereBetween('printed_at', [$this->start, $this->end])
            ->whereNotNull('media')
            ->groupBy('size', 'media', 'gsm')
            ->orderByDesc('events')
            ->get();
    }

    /**
     * Kiosk metrics — the /admin/kiosks module models each row as a
     * store-linked print configuration ("printer_tray"/"print_size" +
     * total_amount), toggled active/inactive via `kiosk` bool.
     */
    public function kiosks(): array
    {
        $counts = $this->kioskCounts();

        $perStore = DB::table('kiosks')
            ->join('stores', 'stores.id', '=', 'kiosks.store_id')
            ->select(
                'stores.id as store_id',
                'stores.store_name',
                DB::raw('COUNT(kiosks.id) as kiosk_count'),
                DB::raw('SUM(CASE WHEN kiosks.kiosk = 1 THEN 1 ELSE 0 END) as active_count'),
                DB::raw('SUM(kiosks.total_amount) as total_amount')
            )
            ->groupBy('stores.id', 'stores.store_name')
            ->orderByDesc('kiosk_count')
            ->get();

        // Kiosk-wise print activity within the window (best-effort match by
        // printer_tray → OrderPrintLog.printer_name / tray_label).
        $kioskActivity = DB::table('kiosks as k')
            ->leftJoin('order_print_logs as l', function ($j) {
                $j->on('l.store_id', '=', 'k.store_id')
                    ->on(function ($sub) {
                        $sub->whereColumn('l.printer_name', 'k.printer_tray')
                            ->orWhereColumn('l.tray_label', 'k.printer_tray');
                    });
            })
            ->select(
                'k.id', 'k.store_id', 'k.printer_tray', 'k.print_size', 'k.kiosk',
                DB::raw('COUNT(CASE WHEN l.printed_at BETWEEN ? AND ? THEN 1 END) as events')
            )
            ->addBinding([$this->start, $this->end], 'select')
            ->groupBy('k.id', 'k.store_id', 'k.printer_tray', 'k.print_size', 'k.kiosk')
            ->orderByDesc('events')
            ->limit(50)
            ->get();

        return [
            'totals'         => $counts,
            'per_store'      => $perStore,
            'kiosk_activity' => $kioskActivity,
        ];
    }

    /**
     * Store-wise combined table — orders + revenue + print events + stages.
     * Every store is listed (including test stores), consistent with the
     * "All Stores" behavior on the dashboard.
     */
    public function perStore(): Collection
    {
        $orderRows = DB::table('orders')
            ->select(
                'store_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw($this->stageSql(self::PICKED_UP) . ' as picked_up_orders'),
                DB::raw($this->stageSql(self::READY)    . ' as ready_orders'),
                DB::raw($this->stageSql(self::PRINTING) . ' as printing_orders'),
                DB::raw($this->newOrdersSql()           . ' as new_orders'),
                DB::raw("SUM(CASE WHEN payment_status = 'paid'    THEN total ELSE 0 END) as paid_amount"),
                DB::raw("SUM(CASE WHEN payment_status = 'pending' THEN total ELSE 0 END) as pending_amount"),
                DB::raw("SUM(CASE WHEN payment_gateway = 'cash' THEN total ELSE 0 END) as cash_amount"),
                DB::raw("SUM(CASE WHEN payment_gateway != 'cash' AND payment_gateway IS NOT NULL THEN total ELSE 0 END) as online_amount")
            )
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('store_id')
            ->get()
            ->keyBy('store_id');

        $printRows = DB::table('order_print_logs')
            ->select('store_id', DB::raw('COUNT(*) as events'))
            ->whereBetween('printed_at', [$this->start, $this->end])
            ->groupBy('store_id')
            ->pluck('events', 'store_id');

        return DB::table('stores')
            ->select('id', 'store_name', 'is_test', 'is_active')
            ->orderBy('store_name')
            ->get()
            ->map(function ($s) use ($orderRows, $printRows) {
                $o = $orderRows->get($s->id);
                return [
                    'store_id'        => $s->id,
                    'store_name'      => $s->store_name,
                    'is_test'         => (bool) $s->is_test,
                    'is_active'       => (bool) $s->is_active,
                    'total_orders'    => (int) ($o->total_orders ?? 0),
                    'new_orders'      => (int) ($o->new_orders ?? 0),
                    'printing_orders' => (int) ($o->printing_orders ?? 0),
                    'ready_orders'    => (int) ($o->ready_orders ?? 0),
                    'picked_up_orders'=> (int) ($o->picked_up_orders ?? 0),
                    'revenue'         => (float) ($o->paid_amount ?? 0),
                    'pending_amount'  => (float) ($o->pending_amount ?? 0),
                    'cash_amount'     => (float) ($o->cash_amount ?? 0),
                    'online_amount'   => (float) ($o->online_amount ?? 0),
                    'print_events'    => (int) ($printRows->get($s->id) ?? 0),
                ];
            });
    }

    /**
     * Daily breakdown for the reporting window. Every date in the window
     * is represented (zero-fill).
     */
    public function daily(): Collection
    {
        $orderDays = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as d'),
                DB::raw('COUNT(*) as orders'),
                DB::raw("SUM(CASE WHEN payment_status='paid' THEN total ELSE 0 END) as revenue"),
                DB::raw($this->stageSql(self::PICKED_UP) . ' as picked_up_orders'),
                DB::raw($this->stageSql(self::READY)    . ' as ready_orders'),
                DB::raw($this->stageSql(self::PRINTING) . ' as printing_orders'),
                DB::raw($this->newOrdersSql()           . ' as new_orders')
            )
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $printDays = DB::table('order_print_logs')
            ->select(DB::raw('DATE(printed_at) as d'), DB::raw('COUNT(*) as events'))
            ->whereBetween('printed_at', [$this->start, $this->end])
            ->groupBy('d')
            ->pluck('events', 'd');

        $rows = collect();
        for ($d = $this->start; $d->lte($this->end); $d = $d->addDay()) {
            $key = $d->toDateString();
            $o = $orderDays->get($key);
            $rows->push([
                'date'             => $key,
                'orders'           => (int) ($o->orders ?? 0),
                'revenue'          => (float) ($o->revenue ?? 0),
                'new_orders'       => (int) ($o->new_orders ?? 0),
                'printing_orders'  => (int) ($o->printing_orders ?? 0),
                'ready_orders'     => (int) ($o->ready_orders ?? 0),
                'picked_up_orders' => (int) ($o->picked_up_orders ?? 0),
                'print_events'     => (int) ($printDays->get($key) ?? 0),
            ]);
        }
        return $rows;
    }

    /**
     * Auto-computed observations — highlights the top store, best-order day,
     * best-revenue day, most-active printer, and pending backlog.
     */
    public function observations(): array
    {
        $perStore = $this->perStore();
        $daily    = $this->daily();
        $printers = $this->printerBreakdown(1);
        $orders   = $this->orders();

        $topStoreByOrders  = $perStore->sortByDesc('total_orders')->first();
        $topStoreByRevenue = $perStore->sortByDesc('revenue')->first();
        $bestOrderDay      = $daily->sortByDesc('orders')->first();
        $bestRevenueDay    = $daily->sortByDesc('revenue')->first();
        $bestPrintDay      = $daily->sortByDesc('print_events')->first();

        return [
            'top_store_orders'  => $topStoreByOrders,
            'top_store_revenue' => $topStoreByRevenue,
            'best_order_day'    => $bestOrderDay,
            'best_revenue_day'  => $bestRevenueDay,
            'best_print_day'    => $bestPrintDay,
            'top_printer'       => $printers->first(),
            'pending_orders'    => $orders['pending_orders'],
            'pending_amount'    => $orders['pending_amount'],
        ];
    }

    /* ─────────── shared aggregates (cached per instance) ─────────── */

    private ?object $_orderAgg = null;
    private ?object $_printAgg = null;

    private function ordersAggregate(): object
    {
        if ($this->_orderAgg) return $this->_orderAgg;

        return $this->_orderAgg = DB::table('orders')
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw($this->stageSql(self::PICKED_UP) . ' as picked_up_orders'),
                DB::raw($this->stageSql(self::READY)    . ' as ready_orders'),
                DB::raw($this->stageSql(self::PRINTING) . ' as printing_orders'),
                DB::raw($this->newOrdersSql()           . ' as new_orders'),
                DB::raw($this->stageSql(self::CANCELLED) . ' as cancelled_orders'),
                DB::raw("SUM(CASE WHEN payment_gateway != 'cash' AND payment_gateway IS NOT NULL THEN total ELSE 0 END) as online_amount"),
                DB::raw("SUM(CASE WHEN payment_gateway = 'cash' THEN total ELSE 0 END) as cash_amount"),
                DB::raw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_orders"),
                DB::raw("SUM(CASE WHEN payment_status = 'pending' THEN total ELSE 0 END) as pending_amount"),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_orders"),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid_amount"),
                DB::raw('SUM(total) as total_value')
            )
            ->whereBetween('created_at', [$this->start, $this->end])
            ->first();
    }

    private function printLogsAggregate(): object
    {
        if ($this->_printAgg) return $this->_printAgg;

        $picked = "'" . implode("','", self::PICKED_UP) . "'";
        $ready  = "'" . implode("','", self::READY)     . "'";
        $prnt   = "'" . implode("','", self::PRINTING)  . "'";
        $canc   = "'" . implode("','", self::CANCELLED) . "'";

        return $this->_printAgg = DB::table('order_print_logs')
            ->join('orders', 'orders.id', '=', 'order_print_logs.order_id')
            ->select(
                DB::raw('COUNT(*) as total_logs'),
                DB::raw('COUNT(DISTINCT order_print_logs.order_id) as distinct_orders'),
                DB::raw("SUM(CASE WHEN order_print_logs.status = 'success' THEN 1 ELSE 0 END) as success_count"),
                DB::raw("SUM(CASE WHEN order_print_logs.status = 'failed'  THEN 1 ELSE 0 END) as failed_count"),
                DB::raw("SUM(CASE WHEN order_print_logs.status = 'retried' THEN 1 ELSE 0 END) as retried_count"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ($picked) THEN 1 ELSE 0 END) as picked_up_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ($ready) THEN 1 ELSE 0 END) as ready_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ($prnt) THEN 1 ELSE 0 END) as printing_orders"),
                DB::raw("SUM(CASE WHEN LOWER(orders.status) IN ('new','pending','confirmed') OR LOWER(orders.status) NOT IN ($picked, $ready, $prnt, $canc) THEN 1 ELSE 0 END) as new_orders")
            )
            ->whereBetween('order_print_logs.printed_at', [$this->start, $this->end])
            ->first();
    }

    private function kioskCounts(): array
    {
        $row = DB::table('kiosks')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN kiosk = 1 THEN 1 ELSE 0 END) as active'),
                DB::raw('SUM(CASE WHEN kiosk = 0 THEN 1 ELSE 0 END) as inactive')
            )->first();
        return [
            'total'    => (int) ($row->total ?? 0),
            'active'   => (int) ($row->active ?? 0),
            'inactive' => (int) ($row->inactive ?? 0),
        ];
    }

    private function stageSql(array $statuses): string
    {
        $list = "'" . implode("','", $statuses) . "'";
        return "SUM(CASE WHEN LOWER(status) IN ($list) THEN 1 ELSE 0 END)";
    }

    private function newOrdersSql(): string
    {
        $picked = "'" . implode("','", self::PICKED_UP) . "'";
        $ready  = "'" . implode("','", self::READY)     . "'";
        $prnt   = "'" . implode("','", self::PRINTING)  . "'";
        $canc   = "'" . implode("','", self::CANCELLED) . "'";
        return "SUM(CASE WHEN LOWER(status) IN ('new','pending','confirmed') OR LOWER(status) NOT IN ($picked, $ready, $prnt, $canc) THEN 1 ELSE 0 END)";
    }
}
