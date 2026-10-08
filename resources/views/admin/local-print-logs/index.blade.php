@extends('layouts.admin')
@section('title', 'Local Print Logs')

@section('content')
    @php
        $statusChip = [
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'sent'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'printing'  => 'bg-blue-50 text-blue-700 border-blue-200',
            'sending'   => 'bg-blue-50 text-blue-700 border-blue-200',
            'pending'   => 'bg-surface-50 text-surface-600 border-surface-200',
            'failed'    => 'bg-rose-50 text-rose-700 border-rose-200',
            'cancelled' => 'bg-amber-50 text-amber-700 border-amber-200',
        ];
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Local Print</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                Every print sent to a local printer via PrintTrays — read only.
                <span class="ml-2 font-mono text-[11px] text-surface-400">
                    {{ number_format($logs->total()) }} record{{ $logs->total() === 1 ? '' : 's' }}
                </span>
            </p>
        </div>
    </div>

    {{-- ── Filter bar ── --}}
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-4 mb-6">
        <form method="GET" action="{{ route('admin.local-print-logs.index') }}"
            class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="File, printer, IP…"
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 min-w-[200px]">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">From</label>
                <input type="date" name="date_start" value="{{ $filters['date_start'] ?? '' }}"
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">To</label>
                <input type="date" name="date_end" value="{{ $filters['date_end'] ?? '' }}"
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Store</label>
                <select name="store_id" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected(($filters['store_id'] ?? '') == $s->id)>{{ $s->store_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">User</label>
                <select name="user_id" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Printer</label>
                <select name="printer_name" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($printers as $p)
                        <option value="{{ $p }}" @selected(($filters['printer_name'] ?? '') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Source</label>
                <select name="source" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($sources as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Origin</label>
                <select name="origin" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($origins as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['origin'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">Status</label>
                <select name="status" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="all">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-surface-500 mb-1">IP</label>
                <input type="text" name="ip" value="{{ $filters['ip'] ?? '' }}" placeholder="192.…"
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 w-[120px]">
            </div>

            <div class="ml-auto flex items-center gap-3">
                <a href="{{ route('admin.local-print-logs.index') }}"
                    class="text-sm text-surface-500 hover:text-brand-600 font-medium">Reset</a>
                <button type="submit"
                    class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
                    Apply
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Date/Time</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Store</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Origin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Printer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">IP / Location</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-surface-50/60 transition">
                            <td class="px-6 py-3 text-sm font-mono text-surface-500">#{{ $log->id }}</td>
                            <td class="px-6 py-3 text-sm text-surface-700 whitespace-nowrap">
                                <div class="font-medium">{{ $log->created_at?->format('M j, Y') }}</div>
                                <div class="text-xs text-surface-400 font-mono">{{ $log->created_at?->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-surface-700">
                                {{ $log->store?->store_name ?? '—' }}
                                @if (!$log->store_id)
                                    <div class="text-[10px] font-mono text-surface-400">local</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-surface-600">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-surface-600">{{ $log->sourceLabel() }}</td>
                            <td class="px-6 py-3 text-sm">
                                @php $isStandalone = $log->origin === \App\Models\LocalPrintLog::ORIGIN_STANDALONE; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-md border {{ $isStandalone ? 'bg-violet-50 text-violet-700 border-violet-200' : 'bg-sky-50 text-sky-700 border-sky-200' }}">
                                    {{ $log->originLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-surface-700">
                                <div class="font-medium">{{ $log->printer_name ?? '—' }}</div>
                                @if ($log->tray_label)
                                    <div class="text-xs text-surface-400">{{ $log->tray_label }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-surface-500">{{ $log->media_size ?? ($log->paper_size ?? '—') }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg border {{ $statusChip[$log->status] ?? 'bg-surface-50 text-surface-600 border-surface-200' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-xs text-surface-500">
                                <div class="font-mono">{{ $log->ip_address ?? '—' }}</div>
                                @php $loc = collect([$log->geo_city, $log->geo_country])->filter()->join(', '); @endphp
                                @if ($loc)
                                    <div class="text-[11px] text-surface-400">{{ $loc }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.local-print-logs.show', $log) }}"
                                    class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    View <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-16 text-center text-sm text-surface-400">
                                No local print logs match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-surface-100">
                {{ $logs->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
