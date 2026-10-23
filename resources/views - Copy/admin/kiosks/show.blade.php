@extends('layouts.kiosks')
@section('title', 'Kiosk Details #' . $kiosk->id)

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.kiosks.index') }}"
                class="inline-flex items-center gap-2 text-sm text-surface-500 hover:text-brand-600 mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Kiosk List
            </a>
            <h1 class="font-display font-bold text-2xl text-surface-900">
                Kiosk Record #{{ $kiosk->id }}
            </h1>
            <p class="text-sm text-surface-500">Created on {{ $kiosk->created_at->format('M d, Y H:i:s') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.kiosks.edit', $kiosk) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">
                <i data-lucide="pencil" class="w-4 h-4"></i> Edit Record
            </a>
            <form action="{{ route('admin.kiosks.destroy', $kiosk) }}" method="POST"
                onsubmit="return confirm('Delete this kiosk record?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 text-sm font-semibold rounded-xl hover:bg-red-100 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-6">
                <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Item
                    Summary</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Store</span>
                        <span class="font-medium text-surface-800 text-base">{{ $kiosk->store->store_name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Product</span>
                        <span
                            class="font-medium text-surface-800 text-base">{{ $kiosk->product->name ?? 'None Associated' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Is Kiosk
                            Order</span>
                        <span
                            class="inline-flex mt-1 px-2.5 py-0.5 text-xs font-semibold rounded-md {{ $kiosk->kiosk ? ' bg-gray-100  text-gray-800' : 'bg-surface-200 text-surface-600' }}">
                            {{ $kiosk->kiosk ? 'Yes (Kiosk)' : 'No (Standard)' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Currency</span>
                        <span class="font-semibold text-surface-800">{{ $kiosk->currency }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-6">
                <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Print
                    Specifications</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Printer
                            Tray</span>
                        <span class="font-medium text-surface-800">{{ $kiosk->printer_tray ?? 'Default Tray' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider">Print
                            Size</span>
                        <span class="font-medium text-surface-800">{{ $kiosk->print_size ?? 'Not specified' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-xs font-semibold text-surface-400 uppercase tracking-wider mb-1">File Path /
                            File Link</span>
                        @if ($kiosk->file_path)
                            <div class="flex items-center gap-3 p-3 bg-surface-50 rounded-xl border border-surface-200">
                                <i data-lucide="file-text" class="w-5 h-5 text-brand-600"></i>
                                <span
                                    class="font-mono text-xs text-surface-800 truncate flex-1">{{ $kiosk->file_path }}</span>
                                <a href="{{ Str::startsWith($kiosk->file_path, ['http://', 'https://']) ? $kiosk->file_path : asset('storage/' . $kiosk->file_path) }}"
                                    target="_blank"
                                    class="px-3 py-1 bg-brand-600 text-white text-xs font-semibold rounded-lg hover:bg-brand-700 transition">
                                    Open / Download
                                </a>
                            </div>
                        @else
                            <span class="text-surface-400 italic">No file path attached</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Card: Financials & Meta -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-4">
                <h2 class="font-display font-semibold text-lg text-surface-900 border-b border-surface-100 pb-3">Pricing
                    Details</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-surface-500">Quantity</span>
                        <span class="font-semibold text-surface-800">{{ $kiosk->quantity }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-surface-500">Unit Price</span>
                        <span class="font-semibold text-surface-800">{{ $kiosk->currency }}
                            {{ number_format($kiosk->price, 2) }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center pt-3 border-t border-surface-100 font-bold text-base text-surface-900">
                        <span>Total Amount</span>
                        <span class="text-brand-600">{{ $kiosk->currency }}
                            {{ number_format($kiosk->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-3">
                <h3 class="font-display font-semibold text-sm text-surface-900 uppercase tracking-wider">Timestamps</h3>
                <div class="text-xs space-y-2 text-surface-500">
                    <p>Created: <span
                            class="text-surface-800 font-medium">{{ $kiosk->created_at->format('M d, Y g:i A') }}</span>
                    </p>
                    <p>Updated: <span
                            class="text-surface-800 font-medium">{{ $kiosk->updated_at->format('M d, Y g:i A') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
