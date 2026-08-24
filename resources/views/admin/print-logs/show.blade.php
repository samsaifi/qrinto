@extends('layouts.admin')
@section('title', 'Print Log #' . $log->id)

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('admin.print-logs.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-surface-500 hover:text-brand-600 mb-2 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Print Logs
            </a>
            <h1 class="font-display font-bold text-2xl text-surface-900">Print event #{{ $log->id }}</h1>
            <p class="text-sm text-surface-500">
                {{ $log->printed_at?->format('M j, Y · H:i:s') }} — read only
            </p>
        </div>
        @php
            $chip = [
                'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'failed'  => 'bg-rose-50 text-rose-700 border-rose-200',
                'retried' => 'bg-amber-50 text-amber-700 border-amber-200',
            ][$log->status] ?? 'bg-surface-50 text-surface-600 border-surface-200';
        @endphp
        <span class="inline-flex items-center px-3 py-1.5 text-sm font-bold rounded-xl border {{ $chip }}">
            {{ ucfirst($log->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Order & user --}}
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-4">Order</h2>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-surface-500">Order #</dt>
                <dd class="col-span-2 font-mono text-surface-800">
                    @if ($log->order)
                        <a href="{{ route('admin.orders.show', $log->order) }}" class="text-brand-600 hover:underline">{{ $log->order_number }}</a>
                    @else
                        {{ $log->order_number ?? '—' }} <span class="text-xs text-surface-400">(deleted)</span>
                    @endif
                </dd>

                <dt class="text-surface-500">Item</dt>
                <dd class="col-span-2 font-mono text-surface-800">#{{ $log->order_item_id ?? '—' }}</dd>

                <dt class="text-surface-500">Store</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->store?->store_name ?? '—' }}</dd>

                <dt class="text-surface-500">Printed by</dt>
                <dd class="col-span-2 text-surface-800">
                    {{ $log->user?->name ?? '—' }}
                    @if ($log->user?->email)
                        <span class="text-xs text-surface-400">({{ $log->user->email }})</span>
                    @endif
                </dd>
            </dl>
        </div>

        {{-- Printer / tray --}}
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-4">Printer</h2>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-surface-500">Printer</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->printer_name }}</dd>

                <dt class="text-surface-500">Tray</dt>
                <dd class="col-span-2 text-surface-800">
                    {{ $log->tray_label ?? '—' }}
                    @if ($log->tray_key)
                        <span class="block text-xs text-surface-400 font-mono">{{ $log->tray_key }}</span>
                    @endif
                </dd>

                <dt class="text-surface-500">Default printer</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->is_default_printer ? 'Yes' : 'No' }}</dd>

                <dt class="text-surface-500">Copies</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->copies }}</dd>
            </dl>
        </div>

        {{-- Media --}}
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-4">Media</h2>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-surface-500">Size</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->size ?? '—' }}</dd>

                <dt class="text-surface-500">Paper</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->media ?? '—' }}</dd>

                <dt class="text-surface-500">gsm</dt>
                <dd class="col-span-2 text-surface-800">{{ $log->gsm ?? '—' }}</dd>

                <dt class="text-surface-500">User Type</dt>
                <dd class="col-span-2 text-surface-800">
                    {{ $log->user_type ? 'UT' . $log->user_type : '—' }}
                </dd>
            </dl>
        </div>

        {{-- File --}}
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-4">PDF snapshot</h2>
            <dl class="grid grid-cols-3 gap-y-3 text-sm">
                <dt class="text-surface-500">Path</dt>
                <dd class="col-span-2 font-mono text-xs text-surface-800 break-all">{{ $log->pdf_path ?? '—' }}</dd>

                <dt class="text-surface-500">Size</dt>
                <dd class="col-span-2 text-surface-800">
                    {{ $log->pdf_bytes ? number_format($log->pdf_bytes) . ' bytes' : '—' }}
                </dd>

                <dt class="text-surface-500">SHA-256</dt>
                <dd class="col-span-2 font-mono text-[11px] text-surface-500 break-all">{{ $log->pdf_sha256 ?? '—' }}</dd>
            </dl>
        </div>

        {{-- Runtime --}}
        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 lg:col-span-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-surface-500 mb-4">Runtime</h2>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <dt class="text-surface-500 text-xs">Duration</dt>
                    <dd class="text-surface-800 font-mono">
                        {{ $log->duration_ms !== null ? $log->duration_ms . ' ms' : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-surface-500 text-xs">QZ Tray version</dt>
                    <dd class="text-surface-800 font-mono">{{ $log->qz_tray_version ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-surface-500 text-xs">Client IP</dt>
                    <dd class="text-surface-800 font-mono">{{ $log->client_ip ?? '—' }}</dd>
                </div>
                <div class="md:col-span-3">
                    <dt class="text-surface-500 text-xs">User Agent</dt>
                    <dd class="text-surface-800 font-mono text-xs break-all">{{ $log->user_agent ?? '—' }}</dd>
                </div>
                @if ($log->error_message)
                    <div class="md:col-span-3">
                        <dt class="text-surface-500 text-xs">Error</dt>
                        <dd class="text-rose-700 bg-rose-50 border border-rose-200 rounded-xl px-3 py-2 text-sm">
                            {{ $log->error_message }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>
@endsection
