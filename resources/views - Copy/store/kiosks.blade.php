@extends('layouts.store')
@section('title', 'Kiosk Logs')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('storepanel.orders') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Orders
            </a>
            <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-900 tracking-tight mt-2 md:mt-3">Kiosk Logs</h1>
            <p class="text-slate-500 mt-1 text-xs md:text-sm">Kiosk print items and logs for {{ $store->store_name ?? 'this store' }}.
            </p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('storepanel.kioskLogs') }}"
        class="bg-white border border-slate-200/80 rounded-2xl p-4 mb-5 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="File path, tray, size..."
                class="rounded-xl border-slate-200 text-sm focus:border-[#287d3c] focus:ring-[#287d3c]">
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Product</label>
            <select name="product_id"
                class="rounded-xl border-slate-200 text-sm focus:border-[#287d3c] focus:ring-[#287d3c]">
                <option value="">All Products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1">Is Kiosk</label>
            <select name="kiosk" class="rounded-xl border-slate-200 text-sm focus:border-[#287d3c] focus:ring-[#287d3c]">
                <option value="">All</option>
                <option value="1" @selected(request('kiosk') === '1')>Yes (Kiosk)</option>
                <option value="0" @selected(request('kiosk') === '0')>No</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-[#287d3c] hover:bg-emerald-800 text-white text-sm font-bold transition">Filter</button>
            <a href="{{ route('storepanel.kioskLogs') }}"
                class="text-sm text-slate-500 hover:text-[#287d3c] transition font-medium">Clear</a>
        </div>
    </form>

    {{-- Mobile card layout --}}
    <div class="md:hidden space-y-3">
        @forelse($kiosks as $kioskItem)
            <div class="bg-white border border-slate-200/80 rounded-xl px-4 py-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="font-semibold text-[13px] text-slate-800">{{ $kioskItem->product->name ?? 'None' }}</span>
                    @if ($kioskItem->kiosk)
                        <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">Kiosk</span>
                    @endif
                </div>
                <div class="flex items-center justify-between text-[12px] text-slate-700">
                    <span>{{ $kioskItem->print_size ?? '-' }} · {{ $kioskItem->printer_tray ?? '-' }}</span>
                    <span class="font-bold text-slate-900">{{ $kioskItem->currency }} {{ number_format($kioskItem->total_amount, 2) }}</span>
                </div>
                <div class="text-[11px] text-slate-500 mt-1">{{ $kioskItem->quantity }} x {{ $kioskItem->currency }} {{ number_format($kioskItem->price, 2) }}</div>
                @if ($kioskItem->file_path)
                    <a href="{{ Str::startsWith($kioskItem->file_path, ['http://', 'https://']) ? $kioskItem->file_path : asset('storage/' . $kioskItem->file_path) }}"
                        target="_blank" class="inline-flex items-center gap-1 text-[11px] font-medium text-[#287d3c] hover:underline mt-1 truncate max-w-full">
                        <i data-lucide="file-text" class="w-3 h-3 flex-shrink-0"></i>
                        <span class="truncate">{{ basename($kioskItem->file_path) }}</span>
                    </a>
                @endif
                <div class="flex items-center justify-between mt-2 text-[11px] text-slate-400">
                    <span class="font-mono">#{{ $kioskItem->id }}</span>
                    <span>{{ $kioskItem->created_at?->format('M j, g:i A') }}</span>
                </div>
            </div>
        @empty
            <div class="px-4 py-12 text-center text-sm text-slate-400">No kiosk logs found for this store.</div>
        @endforelse
        @if ($kiosks->hasPages())
            <div class="mt-4">{{ $kiosks->onEachSide(1)->links() }}</div>
        @endif
    </div>

    {{-- Desktop table --}}
    <div class="hidden md:block bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">ID</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Product</th>
                        <th class="px-3 py-3 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Kiosk</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Print Spec</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">File Path</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Qty x Price</th>
                        <th class="px-5 py-3 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Total</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kiosks as $kioskItem)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">#{{ $kioskItem->id }}</td>
                            <td class="px-5 py-3">
                                <div class="text-sm font-semibold text-slate-800">{{ $kioskItem->product->name ?? 'None' }}</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if ($kioskItem->kiosk)
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">Yes</span>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-600 border border-slate-200">No</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="text-xs font-medium text-slate-800">Size: <span class="font-semibold">{{ $kioskItem->print_size ?? '-' }}</span></div>
                                <div class="text-xs text-slate-500">Tray: <span class="font-medium">{{ $kioskItem->printer_tray ?? '-' }}</span></div>
                            </td>
                            <td class="px-5 py-3 max-w-[220px]">
                                @if ($kioskItem->file_path)
                                    <a href="{{ Str::startsWith($kioskItem->file_path, ['http://', 'https://']) ? $kioskItem->file_path : asset('storage/' . $kioskItem->file_path) }}"
                                        target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#287d3c] hover:underline truncate" title="{{ $kioskItem->file_path }}">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        <span class="truncate">{{ basename($kioskItem->file_path) }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="text-xs text-slate-700">{{ $kioskItem->quantity }} x {{ $kioskItem->currency }} {{ number_format($kioskItem->price, 2) }}</div>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-sm text-slate-900">{{ $kioskItem->currency }} {{ number_format($kioskItem->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600 whitespace-nowrap">
                                <div class="font-medium">{{ $kioskItem->created_at?->format('M j, Y') }}</div>
                                <div class="mono text-[11px] text-slate-400">{{ $kioskItem->created_at?->format('H:i:s') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center text-sm text-slate-400">No kiosk logs found for this store.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kiosks->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">{{ $kiosks->onEachSide(1)->links() }}</div>
        @endif
    </div>
@endsection
