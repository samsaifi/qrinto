<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Kiosk;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

/**
 * Standalone store panel (separate layout from the NAC admin panel).
 * A work queue for store staff: order queue, store QR, and tray setup.
 */
class StorePanelController extends Controller
{
    /**
     * Sort rank for tray labels: Noritsu trays come first ordered by
     * tray number (MP Tray → 0, Tray 1 → 1, …, Tray 5 → 5),
     * everything else sorts after them (rank 100).
     */
    protected static function traySortRank(string $label): int
    {
        if (preg_match('/\(MP\s*Tray\)/i', $label)) return 0;
        if (preg_match('/\(Tray\s*(\d+)\)/i', $label, $m)) return (int) $m[1];
        if (stripos($label, 'Noritsu') !== false) return 50;
        return 100;
    }

    /** Resolve the store this staff member operates. */
    protected function currentStore(): ?Store
    {
        $user = auth()->user();

        if ($user && $user->store_id) {
            return $user->store;
        }

        // NAC admins without a store see the first real store as a preview.
        return Store::realStores()->active()->first() ?? Store::first();
    }

    public function orders(Request $request)
    {
        $store = $this->currentStore();

        // Trays gate: the picker on this page prints to configured trays only.
        // If the store hasn't set up any (or none are enabled), send the
        // operator to /store/trays first so they can't hit "Print on 931BL"
        // with an empty picker.
        if ($store) {
            $hasEnabledTray = collect($store->trayRows())->contains('enabled', true);
            if (!$hasEnabledTray) {
                return redirect()
                    ->route('storepanel.trays')
                    ->with('warning', 'Set up at least one tray before printing orders.');
            }
        }

        $query = Order::with('user', 'items.product', 'store')
            ->where('created_at', '>=', now()->subDays(30));

        if ($store) {
            $query->where('store_id', $store->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->get();

        $queue = [
            Order::STAGE_NEW      => collect(),
            Order::STAGE_PRINTING => collect(),
            Order::STAGE_READY    => collect(),
        ];
        $doneCollection = collect();

        foreach ($orders as $order) {
            $stage = $order->queue_stage;
            if (isset($queue[$stage])) {
                $queue[$stage]->push($order);
            } elseif ($stage === Order::STAGE_DONE) {
                $doneCollection->push($order);
            }
        }

        // Picked-up section: only the most recent 5 per page, with an
        // independent `done_page` query string so it doesn't collide with
        // other pagination on the page.
        $donePerPage = 5;
        $donePageName = 'done_page';
        $donePage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($donePageName);
        $recentlyDone = new \Illuminate\Pagination\LengthAwarePaginator(
            $doneCollection->forPage($donePage, $donePerPage)->values(),
            $doneCollection->count(),
            $donePerPage,
            $donePage,
            ['path' => $request->url(), 'pageName' => $donePageName, 'query' => $request->query()]
        );

        return view('store.orders', [
            'store'        => $store,
            'queue'        => $queue,
            'recentlyDone' => $recentlyDone,
        ]);
    }

    public function ordersV2(Request $request)
    {
        $store = $this->currentStore();

        if ($store) {
            $hasEnabledTray = collect($store->trayRows())->contains('enabled', true);
            if (!$hasEnabledTray) {
                return redirect()
                    ->route('storepanel.trays')
                    ->with('warning', 'Set up at least one tray before printing orders.');
            }
        }

        $query = Order::with('user', 'items.product', 'store')
            ->where('created_at', '>=', now()->subDays(30));

        if ($store) {
            $query->where('store_id', $store->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->get();

        $queue = [
            Order::STAGE_NEW      => collect(),
            Order::STAGE_PRINTING => collect(),
            Order::STAGE_READY    => collect(),
        ];
        $doneCollection = collect();

        foreach ($orders as $order) {
            $stage = $order->queue_stage;
            if (isset($queue[$stage])) {
                $queue[$stage]->push($order);
            } elseif ($stage === Order::STAGE_DONE) {
                $doneCollection->push($order);
            }
        }

        $donePerPage = 5;
        $donePageName = 'done_page';
        $donePage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($donePageName);
        $recentlyDone = new \Illuminate\Pagination\LengthAwarePaginator(
            $doneCollection->forPage($donePage, $donePerPage)->values(),
            $doneCollection->count(),
            $donePerPage,
            $donePage,
            ['path' => $request->url(), 'pageName' => $donePageName, 'query' => $request->query()]
        );

        return view('store.orders-v2', [
            'store'        => $store,
            'queue'        => $queue,
            'recentlyDone' => $recentlyDone,
        ]);
    }

    /**
     * Store-scoped view of order_print_logs — 20 per page, current store
     * only. Read-only; the store panel never edits or deletes logs.
     */
    public function printLogs(Request $request)
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to show logs for.');

        $query = \App\Models\OrderPrintLog::with(['order:id,order_number', 'user:id,name'])
            ->where('store_id', $store->id)
            ->orderByDesc('printed_at');

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

        $logs = $query->paginate(20)->withQueryString();

        return view('store.print-logs', compact('store', 'logs'));
    }

    /**
     * Store-scoped view of kiosks — 15 per page, current store only.
     * Read-only for store panel staff.
     */
    public function kioskLogs(Request $request)
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to show kiosk logs for.');

        $query = Kiosk::with(['store', 'product'])
            ->where('store_id', $store->id);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('kiosk')) {
            $query->where('kiosk', $request->kiosk === '1');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('file_path', 'like', "%{$search}%")
                  ->orWhere('printer_tray', 'like', "%{$search}%")
                  ->orWhere('print_size', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $kiosks = $query->latest()->paginate(15)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('store.kiosks', compact('store', 'kiosks', 'products'));
    }

    /**
     * Log a print attempt (success or failure) against an order.
     * Called by the browser right after QZ Tray reports the outcome.
     * Only the client-observed fields come from the request; everything
     * authoritative (user, ip, ua, file stats) is filled server-side.
     */
    public function logPrint(Request $request, \App\Models\Order $order)
    {
        $store = $this->currentStore();
        abort_if($store && $order->store_id !== $store->id, 403, 'Not this store\'s order.');

        $data = $request->validate([
            'printer_name'       => 'required|string|max:160',
            'tray_key'           => 'nullable|string|max:120',
            'tray_label'         => 'nullable|string|max:160',
            'is_default_printer' => 'nullable|boolean',
            'copies'             => 'nullable|integer|min:1|max:99',
            'size'               => 'nullable|string|max:20',
            'media'              => 'nullable|string|max:40',
            'gsm'                => 'nullable|string|max:20',
            'user_type'          => 'nullable|integer|min:1|max:7',
            'status'             => 'required|in:success,failed,retried',
            'error_message'      => 'nullable|string|max:2000',
            'duration_ms'        => 'nullable|integer|min:0',
            'qz_tray_version'    => 'nullable|string|max:32',
            'order_item_id'      => 'nullable|integer|exists:order_items,id',
        ]);

        // Server-authoritative file snapshot: find the item with a PDF.
        $order->loadMissing('items');
        $item = $data['order_item_id'] ?? null
            ? $order->items->firstWhere('id', $data['order_item_id'])
            : $order->items->firstWhere(fn($i) => !empty($i->pdf_path));

        $pdfPath = $item?->pdf_path;
        $pdfBytes = null;
        $pdfHash = null;
        if ($pdfPath) {
            $abs = storage_path('app/public/' . ltrim($pdfPath, '/'));
            if (is_file($abs)) {
                $pdfBytes = @filesize($abs) ?: null;
                $pdfHash  = @hash_file('sha256', $abs) ?: null;
            }
        }

        $log = \App\Models\OrderPrintLog::create([
            'order_id'           => $order->id,
            'order_item_id'      => $item?->id,
            'store_id'           => $order->store_id,
            'user_id'            => auth()->id(),
            'order_number'       => $order->order_number,
            'printed_at'         => now(),
            'printer_name'       => $data['printer_name'],
            'tray_key'           => $data['tray_key'] ?? null,
            'tray_label'         => $data['tray_label'] ?? null,
            'is_default_printer' => (bool) ($data['is_default_printer'] ?? false),
            'copies'             => (int) ($data['copies'] ?? 1),
            'size'               => $data['size']  ?? null,
            'media'              => $data['media'] ?? null,
            'gsm'                => $data['gsm']   ?? null,
            'user_type'          => $data['user_type'] ?? null,
            'pdf_path'           => $pdfPath,
            'pdf_bytes'          => $pdfBytes,
            'pdf_sha256'         => $pdfHash,
            'status'             => $data['status'],
            'error_message'      => $data['error_message'] ?? null,
            'duration_ms'        => $data['duration_ms'] ?? null,
            'client_ip'          => $request->ip(),
            'user_agent'         => substr((string) $request->userAgent(), 0, 512),
            'qz_tray_version'    => $data['qz_tray_version'] ?? null,
        ]);

        return response()->json(['ok' => true, 'id' => $log->id]);
    }

    /**
     * Return the QZ Tray signing certificate (PEM, public).
     */
    public function qzCertificate()
    {
        $path = storage_path('app/qz-certs/qz-cert.pem');
        abort_if(!is_file($path), 404, 'QZ certificate not found.');
        return response(file_get_contents($path))->header('Content-Type', 'text/plain');
    }

    /**
     * Sign a QZ Tray request with the private key so the Allow/Deny
     * dialog is skipped on trusted installs.
     */
    public function qzSign(Request $request)
    {
        $request->validate(['request' => 'required|string|max:10000']);

        $keyPath = storage_path('app/qz-certs/qz-private.pem');
        abort_if(!is_file($keyPath), 404, 'QZ private key not found.');

        $key = openssl_pkey_get_private(file_get_contents($keyPath));
        abort_if(!$key, 500, 'Could not load QZ private key.');

        $signature = '';
        $ok = openssl_sign($request->input('request'), $signature, $key, OPENSSL_ALGO_SHA512);
        abort_if(!$ok, 500, 'Signing failed.');

        return response(base64_encode($signature))->header('Content-Type', 'text/plain');
    }

    /**
     * Persist that the current user has installed QZ Tray on this PC.
     * Called from the "Already Downloaded" button on /store/orders.
     */
    public function confirmQzTray(Request $request)
    {
        \App\Models\UserQzTrayConfirmation::updateOrCreate(
            ['user_id' => auth()->id()],
            ['confirmed_at' => now()],
        );

        return response()->json(['ok' => true]);
    }

    public function qr()
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to show a QR for.');

        $scanUrl = url('/store/' . $store->store_code);

        return view('store.qr', compact('store', 'scanUrl'));
    }

    public function trays()
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to configure.');

        // Only show the "Do you have QZ Tray?" onboarding modal to a user
        // who has not yet clicked "Already Downloaded" in the past. Once
        // they confirm, the row in user_qz_tray_confirmations sticks and
        // this returns false forever after.
        $needsQzOnboarding = !\App\Models\UserQzTrayConfirmation::where('user_id', auth()->id())->exists();

        return view('store.trays', [
            'store'             => $store,
            'rows'              => $store->trayRows(),
            'sizeOptions'       => Store::traySizeOptions(),
            'sizeDims'          => Store::traySizeDimensions(),
            'mediaOptions'      => Store::trayMediaOptions(),
            'gsmOptions'        => Store::trayGsmOptions(),
            'needsQzOnboarding' => $needsQzOnboarding,
        ]);
    }

    /**
     * Prepare a QZ Tray print payload for an order: the matched tray, the
     * printer queue to target, and the print-ready PDF URL. The store panel's
     * front-end calls this before dispatching to QZ Tray.
     */
    public function preparePrint(\App\Models\Order $order)
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to print for.');
        abort_if($order->store_id !== $store->id, 403, 'Not this store\'s order.');

        // Self-heal the PDF if missing.
        try {
            app(\App\Services\OrderPdfService::class)->ensureOrderPdfsExist($order);
        } catch (\Throwable $e) {
            \Log::error('preparePrint self-heal failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        $order->loadMissing('items');
        $item = $order->items->firstWhere(fn($i) => !empty($i->pdf_path));

        if (!$item || !$item->pdf_path) {
            return response()->json(['ok' => false, 'error' => 'No print-ready PDF for this order yet.'], 422);
        }

        $flow      = $order->flow_data ?? [];
        $sizeCode  = $flow['size_slug']
            ?? strtolower(str_replace([' ', '×'], ['', 'x'], (string) ($flow['size_dimensions'] ?? '')));
        $matched   = $store->matchTrayForSize($sizeCode ?: null);

        $copies = 1;
        foreach ($order->items as $i) { $copies = max($copies, (int) ($i->quantity ?: 1)); }

        // Return every tray so the operator picks — the one that auto-matches
        // is flagged `recommended` so the UI can highlight it.
        $mediaLabels = \App\Models\Store::trayMediaOptions();
        $sizePretty  = ['4x6' => '4 × 6', '5x7' => '5 × 7', '7x10' => '7 × 10', '8.5x11' => '8.5 × 11'];

        $trays = collect($store->trayRows())->where('enabled', true)->map(function ($t) use ($matched, $mediaLabels, $sizePretty) {
            return [
                'key'         => $t['key'],
                'label'       => $t['label'],
                'size'        => $t['size'],
                'size_pretty' => $sizePretty[$t['size']] ?? $t['size'],
                'size_width'  => $t['size_width'] ?? null,
                'size_height' => $t['size_height'] ?? null,
                'media'       => $t['media'],
                'media_label' => $mediaLabels[$t['media']] ?? $t['media'],
                'gsm'         => $t['gsm'],
                'density'     => $t['density'] ?? null,
                'user_type'   => $t['user_type'],
                'enabled'     => (bool) $t['enabled'],
                'printer'     => $t['printer'] ?? '',
                'recommended' => $matched && $matched['key'] === $t['key'],
            ];
        })->sort(function ($a, $b) {
            $aRank = self::traySortRank($a['label']);
            $bRank = self::traySortRank($b['label']);
            return $aRank <=> $bRank ?: strcasecmp($a['label'], $b['label']);
        })->values()->all();

        // Embed the PDF bytes as base64 so QZ Tray doesn't have to fetch a
        // URL from the desktop process (which has no browser cookies and is
        // fragile to APP_URL / storage-symlink / doc-root differences across
        // deployments). URL is kept as an informational fallback.
        $absPath = storage_path('app/public/' . ltrim($item->pdf_path, '/'));
        $pdfBase64 = null;
        if (is_file($absPath)) {
            $bytes = @file_get_contents($absPath);
            if ($bytes !== false && substr($bytes, 0, 4) === '%PDF') {
                $pdfBase64 = base64_encode($bytes);
            }
        }

        if (!$pdfBase64) {
            return response()->json([
                'ok' => false,
                'error' => 'The print-ready PDF could not be read from disk.',
            ], 422);
        }

        return response()->json([
            'ok'          => true,
            'order'       => [
                'id'      => $order->id,
                'item_id' => $item->id,
                'number'  => $order->order_number,
                'size'    => isset($flow['size_width'], $flow['size_height'])
                    ? $flow['size_width'] . ' × ' . $flow['size_height'] . ' ' . ($flow['size_unit'] ?? 'inch')
                    : ($sizeCode ?: null),
            ],
            'trays'       => $trays,
            'matched_key' => $matched['key'] ?? null,
            'pdf_url'     => asset('storage/' . ltrim($item->pdf_path, '/')),
            'pdf_base64'  => $pdfBase64,
            'copies'      => $copies,
            'advance_url' => route('store.orders.advance', $order),
            'log_url'     => route('storepanel.orders.logPrint', $order),
        ]);
    }

    public function saveTrays(Request $request)
    {
        $store = $this->currentStore();
        abort_if(!$store, 404, 'No store to configure.');

        $data = $request->validate([
            'trays'               => 'required|array',
            'trays.*.printer'     => 'required|string|max:160',
            'trays.*.size'        => 'nullable|string|max:120',
            'trays.*.size_width'  => 'nullable|numeric|min:0|max:100',
            'trays.*.size_height' => 'nullable|numeric|min:0|max:100',
            'trays.*.media'       => 'nullable|string|in:' . implode(',', array_keys(Store::trayMediaOptions())),
            'trays.*.gsm'         => 'nullable|string|in:' . implode(',', array_keys(Store::trayGsmOptions())),
            'trays.*.density'     => 'nullable|string|max:20',
            'trays.*.enabled'     => 'nullable',
        ]);

        // Deduplicate by printer name (slugified key) so a double-scan can't
        // save the same printer twice.
        $config = [];
        foreach ($data['trays'] as $t) {
            $printer = trim((string) ($t['printer'] ?? ''));
            if ($printer === '') continue;
            $key = Store::trayKeyFromPrinter($printer);
            $config[$key] = [
                'key'         => $key,
                'label'       => $printer,
                'printer'     => $printer,
                'size'        => $t['size']  ?? null,
                'size_width'  => isset($t['size_width'])  && $t['size_width'] !== ''  ? (float) $t['size_width']  : null,
                'size_height' => isset($t['size_height']) && $t['size_height'] !== '' ? (float) $t['size_height'] : null,
                'media'       => $t['media'] ?? null,
                'gsm'         => $t['gsm']   ?? null,
                'density'     => $t['density'] ?? null,
                'enabled'     => (bool) ($t['enabled'] ?? false),
            ];
        }

        $store->update(['tray_config' => array_values($config)]);

        return redirect()->route('storepanel.trays')->with('success', 'Tray setup saved.');
    }
}
