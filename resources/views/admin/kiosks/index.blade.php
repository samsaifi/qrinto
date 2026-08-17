@extends('layouts.kiosks')
@section('title', 'Kiosk Items')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Kiosk Management</h1>
            <p class="text-sm text-surface-500">Manage kiosk print items, tray configurations, and files</p>
        </div>
        <a href="{{ route('admin.kiosks.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Kiosk Record
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
        <form action="{{ route('admin.kiosks.index') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="File path, tray, size..."
                    class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            @if (auth()->user()->isAdmin())
                <div>
                    <label class="block text-xs font-semibold text-surface-500 mb-1">Store</label>
                    <select name="store_id"
                        class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Stores</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                {{ $store->store_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Product</label>
                <select name="product_id"
                    class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">All Products</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Is Kiosk</label>
                <select name="kiosk"
                    class="w-full rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">All</option>
                    <option value="1" {{ request('kiosk') === '1' ? 'selected' : '' }}>Yes (Kiosk)</option>
                    <option value="0" {{ request('kiosk') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Filter</button>
                <a href="{{ route('admin.kiosks.index') }}"
                    class="px-3 py-2 text-sm text-surface-500 hover:text-brand-600 border border-surface-200 rounded-xl">Clear</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="bg-surface-50 border-b border-surface-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">ID
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">
                            Store / Product</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider">
                            Kiosk</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">
                            Print Spec</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">File
                            Path</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase tracking-wider">Qty
                            x Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-surface-500 uppercase tracking-wider">
                            Total</th>
                        <th
                            class="px-4 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-32">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @forelse($kiosks as $kioskItem)
                        <tr class="hover:bg-surface-50/60 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-surface-500">
                                #{{ $kioskItem->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-semibold text-surface-800">
                                    {{ $kioskItem->store->store_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-surface-500">
                                    Product: {{ $kioskItem->product->name ?? 'None' }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if ($kioskItem->kiosk)
                                    <span
                                        class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md  bg-gray-100  text-gray-800">
                                        Yes
                                    </span>
                                @else
                                    <span
                                        class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md bg-surface-200 text-surface-600">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-surface-800">
                                    Size: <span class="font-semibold">{{ $kioskItem->print_size ?? '—' }}</span>
                                </div>
                                <div class="text-xs text-surface-500">
                                    Tray: <span class="font-medium">{{ $kioskItem->printer_tray ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 max-w-[200px]">
                                @if ($kioskItem->file_path)
                                    <a href="{{ Str::startsWith($kioskItem->file_path, ['http://', 'https://']) ? $kioskItem->file_path : asset('storage/' . $kioskItem->file_path) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-brand-600 hover:text-brand-700 truncate"
                                        title="{{ $kioskItem->file_path }}">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5 flex-shrink-0"></i>
                                        <span class="truncate">{{ basename($kioskItem->file_path) }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-surface-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="text-xs text-surface-700">
                                    {{ $kioskItem->quantity }} x {{ $kioskItem->currency }}
                                    {{ number_format($kioskItem->price, 2) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-sm text-surface-900">
                                {{ $kioskItem->currency }} {{ number_format($kioskItem->total_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.kiosks.show', $kioskItem) }}"
                                        class="inline-flex p-1.5 rounded-lg hover:bg-surface-100 text-surface-400 hover:text-surface-700 transition"
                                        title="View Details">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.kiosks.edit', $kioskItem) }}"
                                        class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.kiosks.destroy', $kioskItem) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this kiosk record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition"
                                            title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-surface-400">
                                No kiosk records found. <a href="{{ route('admin.kiosks.create') }}"
                                    class="text-brand-600 font-medium">Add your first kiosk record</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kiosks->hasPages())
            <div class="px-6 py-4 border-t border-surface-100">{{ $kiosks->links() }}</div>
        @endif
    </div>
@endsection
