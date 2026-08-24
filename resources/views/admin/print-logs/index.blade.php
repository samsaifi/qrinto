@extends('layouts.admin')
@section('title', 'Print Logs')

@section('content')
    @php
        // Human label for the currently-selected preset (shown in the header).
        $presetLabels = [
            'today'  => 'Today',
            'week'   => 'This Week (last 7 days)',
            'month'  => 'This Month',
            'year'   => 'This Year',
            'all'    => 'All Time',
            'custom' => 'Custom range',
        ];
        $rangeLabel = $preset === 'custom' && $dateStart && $dateEnd
            ? \Carbon\Carbon::parse($dateStart)->format('M j, Y') . ' – ' . \Carbon\Carbon::parse($dateEnd)->format('M j, Y')
            : ($presetLabels[$preset] ?? 'This Week');

        // Store label for the header chip.
        $activeStore = null;
        if ($storeId !== 'all') {
            $activeStore = $stores->firstWhere('id', (int) $storeId);
        }
        $storeLabel = $activeStore?->store_name ?? 'All stores';

        // Query preserved when Export button is clicked.
        $exportParams = array_filter([
            'preset'     => $preset,
            'date_start' => $preset === 'custom' ? $dateStart : null,
            'date_end'   => $preset === 'custom' ? $dateEnd   : null,
            'store_id'   => $storeId !== 'all' ? $storeId : null,
            'search'     => request('search'),
            'status'     => request('status'),
        ], fn($v) => $v !== null && $v !== '');
    @endphp

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Print Logs</h1>
            <p class="text-sm text-surface-500 mt-0.5">
                Every QZ Tray print event — read only.
                <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200 text-[11px] font-semibold">
                    <i data-lucide="calendar" class="w-3 h-3"></i> {{ $rangeLabel }}
                </span>
                <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full bg-surface-100 text-surface-700 border border-surface-200 text-[11px] font-semibold">
                    <i data-lucide="store" class="w-3 h-3"></i> {{ $storeLabel }}
                </span>
            </p>
        </div>
        <a href="{{ route('admin.print-logs.export', $exportParams) }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition shadow-sm">
            <i data-lucide="download" class="w-4 h-4"></i> Export Excel
        </a>
    </div>

    {{-- ── Combined filter (analytics + search/status) ── --}}
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-5">
        <form method="GET" action="{{ route('admin.print-logs.index') }}"
            x-data="{ preset: '{{ $preset }}' }" class="space-y-4">

            {{-- Row 1: presets + custom range + store --}}
            <div class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="preset" x-model="preset">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ([
                        'today'  => 'Today',
                        'week'   => 'This Week',
                        'month'  => 'This Month',
                        'year'   => 'This Year',
                        'all'    => 'All Time',
                        'custom' => 'Custom',
                    ] as $key => $label)
                        <button type="button"
                            @click="preset = '{{ $key }}'"
                            :class="preset === '{{ $key }}' ? 'bg-brand-600 text-white' : 'bg-surface-100 text-surface-600 hover:bg-surface-200'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-end gap-2" x-show="preset === 'custom'" x-cloak>
                    <div>
                        <label class="block text-[11px] font-semibold text-surface-500 mb-1">From</label>
                        <input type="date" name="date_start" value="{{ $preset === 'custom' ? $dateStart : '' }}"
                            class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-surface-500 mb-1">To</label>
                        <input type="date" name="date_end" value="{{ $preset === 'custom' ? $dateEnd : '' }}"
                            class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>
                </div>

                <div class="ml-auto">
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Store</label>
                    <select name="store_id" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 min-w-[190px]">
                        <option value="all">All stores</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}" @selected($storeId === (string) $s->id)>
                                {{ $s->store_name }}@if ($s->is_test) (Test) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Row 2: search + status + submit --}}
            <div class="flex flex-wrap items-end gap-3 pt-3 border-t border-surface-100">
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Order #, printer, tray…"
                        class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500 min-w-[240px]">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-surface-500 mb-1">Status</label>
                    <select name="status" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach (['success', 'failed', 'retried'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ml-auto flex items-center gap-3">
                    <a href="{{ route('admin.print-logs.index') }}"
                        class="text-sm text-surface-500 hover:text-brand-600 font-medium">Reset</a>
                    <button type="submit"
                        class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
                        Apply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Analytics summary cards ── --}}
    @php
        $cards = [
            ['label' => 'Total New',              'value' => $analytics['new_orders'],       'icon' => 'inbox',         'tint' => 'bg-blue-50 text-blue-700 border-blue-200'],
            ['label' => 'Total Printing',         'value' => $analytics['printing_orders'],  'icon' => 'printer',       'tint' => 'bg-purple-50 text-purple-700 border-purple-200'],
            ['label' => 'Total Ready for Pickup', 'value' => $analytics['ready_orders'],     'icon' => 'package-check', 'tint' => 'bg-amber-50 text-amber-700 border-amber-200'],
            ['label' => 'Total Picked Up',        'value' => $analytics['picked_up_orders'], 'icon' => 'check-circle-2','tint' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach ($cards as $c)
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-surface-500 uppercase tracking-wide">{{ $c['label'] }}</span>
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border {{ $c['tint'] }}">
                        <i data-lucide="{{ $c['icon'] }}" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="text-3xl font-display font-bold text-surface-900">{{ number_format($c['value']) }}</div>
            </div>
        @endforeach
    </div>
    <p class="text-[11px] text-surface-400 -mt-3 mb-4 font-mono">
        {{ number_format($analytics['total_logs']) }} print event{{ $analytics['total_logs'] === 1 ? '' : 's' }}
        across {{ number_format($analytics['total_orders']) }} order{{ $analytics['total_orders'] === 1 ? '' : 's' }}
        in this range.
    </p>

    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">When</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Store</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Printer</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Media</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-surface-50/60 transition">
                            <td class="px-6 py-3 text-sm text-surface-700 whitespace-nowrap">
                                <div class="font-medium">{{ $log->printed_at?->format('M j, Y') }}</div>
                                <div class="text-xs text-surface-400 font-mono">{{ $log->printed_at?->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <span class="font-mono font-semibold text-surface-800">{{ $log->order_number ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-surface-700">{{ $log->store?->store_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-surface-700">
                                <div class="font-medium">{{ $log->printer_name }}</div>
                                @if ($log->tray_label && $log->tray_label !== $log->printer_name)
                                    <div class="text-xs text-surface-400">{{ $log->tray_label }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-surface-500">
                                {{ collect([$log->size, $log->media, $log->gsm])->filter()->join(' · ') ?: '—' }}
                                @if ($log->user_type)
                                    <div class="text-[10px] font-mono text-surface-400">UT{{ $log->user_type }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-surface-600">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $chip = [
                                        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'failed'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'retried' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ][$log->status] ?? 'bg-surface-50 text-surface-600 border-surface-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg border {{ $chip }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.print-logs.show', $log) }}"
                                    class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    View <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-sm text-surface-400">
                                No print events in this range.
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
