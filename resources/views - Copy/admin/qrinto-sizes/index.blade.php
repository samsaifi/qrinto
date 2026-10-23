@extends('layouts.admin')
@section('title', 'Qrinto Sizes')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Qrinto Sizes</h1>
            <p class="text-sm text-surface-500">Manage available sizes and pricing for qrinto uploads</p>
        </div>
        <a href="{{ route('admin.qrinto-sizes.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Size
        </a>
    </div>

    <!-- Info Card -->
    <div class="mb-6 p-4  bg-gray-50 border  border-gray-200  text-gray-800 rounded-xl flex items-center gap-3 text-sm">
        <i data-lucide="info" class="w-5 h-5  text-gray-600 flex-shrink-0"></i>
        <span>These sizes and prices are displayed to customers on the <strong>Qrinto</strong> page when they upload their
            own designs.</span>
    </div>

    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Label</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Dimensions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Popular</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-surface-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-surface-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100">
                    @forelse($sizes as $size)
                        <tr class="hover:bg-surface-50 transition">
                            <td class="px-6 py-4 text-sm text-surface-400 font-mono">{{ $size->sort_order }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-brand-100 text-brand-700 font-bold text-sm">
                                    {{ $size->key }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-surface-800">{{ $size->label }}</td>
                            <td class="px-6 py-4 text-sm text-surface-600">{{ $size->dimensions }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="font-bold text-surface-900">{{ \App\Services\CurrencyService::format($size->price) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($size->is_popular)
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-100 text-brand-700">
                                        <i data-lucide="star" class="w-3 h-3 inline-block -mt-0.5"></i> Popular
                                    </span>
                                @else
                                    <span class="text-xs text-surface-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $size->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                                    {{ $size->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.qrinto-sizes.edit', $size) }}"
                                        class="p-2 rounded-lg hover:bg-surface-100 text-surface-500 hover:text-brand-600 transition"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.qrinto-sizes.destroy', $size) }}" method="POST"
                                        onsubmit="return confirm('Delete this size? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 rounded-lg hover:bg-red-50 text-surface-500 hover:text-red-600 transition"
                                            title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 bg-surface-100 rounded-xl flex items-center justify-center">
                                        <i data-lucide="ruler" class="w-6 h-6 text-surface-300"></i>
                                    </div>
                                    <p class="text-surface-400">No qrinto sizes configured yet.</p>
                                    <a href="{{ route('admin.qrinto-sizes.create') }}"
                                        class="text-sm text-brand-600 hover:text-brand-700 font-semibold">Add your first
                                        size →</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
