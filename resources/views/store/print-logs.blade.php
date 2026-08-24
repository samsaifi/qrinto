@extends('layouts.store')
@section('title', 'Print logs')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('storepanel.orders') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Orders
            </a>
            <h1 class="font-display font-bold text-3xl text-slate-900 tracking-tight mt-3">Print logs</h1>
            <p class="text-slate-500 mt-1 text-sm">Every print event from this store.</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('storepanel.printLogs') }}"
        class="bg-white border border-slate-200/80 rounded-2xl p-4 mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Order #, printer, tray…"
                class="rounded-xl border-slate-200 text-sm focus:border-[#287d3c] focus:ring-[#287d3c]">
        </div>
        <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Status</label>
            <select name="status"
                class="rounded-xl border-slate-200 text-sm focus:border-[#287d3c] focus:ring-[#287d3c]">
                <option value="">All</option>
                @foreach (['success', 'failed', 'retried'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition">Filter</button>
        <a href="{{ route('storepanel.printLogs') }}"
            class="text-sm text-slate-500 hover:text-[#287d3c] transition font-medium">Clear</a>
    </form>

    {{-- Log table --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">When</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Order</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Printer</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Media</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">By</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3 text-sm text-slate-700 whitespace-nowrap">
                                <div class="font-medium">{{ $log->printed_at?->format('M j, Y') }}</div>
                                <div class="mono text-[11px] text-slate-400">{{ $log->printed_at?->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm">
                                <span class="mono font-semibold text-slate-800">{{ $log->order_number ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-700">
                                <div class="font-medium">{{ $log->printer_name }}</div>
                                @if ($log->tray_label && $log->tray_label !== $log->printer_name)
                                    <div class="text-[11px] text-slate-400">{{ $log->tray_label }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-[12px] text-slate-500">
                                {{ collect([$log->size, $log->media, $log->gsm])->filter()->join(' · ') ?: '—' }}
                                @if ($log->user_type)
                                    <div class="mono text-[10px] text-slate-400">UT{{ $log->user_type }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $chip = [
                                        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'failed'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'retried' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ][$log->status] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-lg border {{ $chip }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-sm text-slate-400">
                                No print events yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $logs->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
