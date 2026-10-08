<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocalPrintLog;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * NAC-admin view of locally generated print logs (spec §7–9). Read-only listing
 * with filters + a detail view that surfaces the complete PrintTrays payloads.
 */
class LocalPrintLogController extends Controller
{
    public function index(Request $request)
    {
        $query = LocalPrintLog::with(['store:id,store_name,store_code', 'user:id,name,email'])
            ->orderByDesc('created_at');

        // Free-text search across file name, printer and IP.
        if ($request->filled('search')) {
            $q = trim((string) $request->search);
            $query->where(function ($w) use ($q) {
                $w->where('file_name', 'like', "%{$q}%")
                    ->orWhere('printer_name', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        // Date range (created_at).
        if ($request->filled('date_start')) {
            $query->where('created_at', '>=', now()->parse($request->date_start)->startOfDay());
        }
        if ($request->filled('date_end')) {
            $query->where('created_at', '<=', now()->parse($request->date_end)->endOfDay());
        }

        // Scalar filters.
        foreach ([
            'store_id'     => 'store_id',
            'user_id'      => 'user_id',
            'printer_name' => 'printer_name',
            'status'       => 'status',
            'source'       => 'source',
            'origin'       => 'origin',
        ] as $param => $column) {
            if ($request->filled($param) && $request->input($param) !== 'all') {
                $query->where($column, $request->input($param));
            }
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . trim((string) $request->ip) . '%');
        }

        // Sorting.
        $sort = $request->input('sort', 'created_at');
        $dir  = strtolower($request->input('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sort, ['created_at', 'status', 'printer_name', 'source', 'origin', 'copies'], true)) {
            $query->reorder($sort, $dir);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.local-print-logs.index', [
            'logs'     => $logs,
            'stores'   => Store::orderBy('store_name')->get(['id', 'store_name']),
            'users'    => User::orderBy('name')->get(['id', 'name']),
            'printers' => LocalPrintLog::query()->whereNotNull('printer_name')
                ->distinct()->orderBy('printer_name')->pluck('printer_name'),
            'sources'  => LocalPrintLog::SOURCES,
            'origins'  => LocalPrintLog::ORIGINS,
            'statuses' => ['pending', 'sending', 'sent', 'printing', 'completed', 'failed', 'cancelled'],
            'filters'  => $request->only(['search', 'date_start', 'date_end', 'store_id', 'user_id', 'printer_name', 'status', 'source', 'origin', 'ip', 'sort', 'dir']),
        ]);
    }

    public function show(LocalPrintLog $localPrintLog)
    {
        $localPrintLog->load(['store', 'user']);
        return view('admin.local-print-logs.show', ['log' => $localPrintLog]);
    }
}
