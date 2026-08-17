@extends('layouts.admin')
@section('title', 'Manage Documentation Guides (CMS)')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-surface-900">Documentation Management (CMS)</h1>
                <p class="text-xs text-surface-500">Add, edit, publish, or remove non-technical employee guides.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.docs.index') }}"
                    class="px-4 py-2 bg-surface-100 hover:bg-surface-200 text-surface-800 text-xs font-semibold rounded-xl border border-surface-200 transition">
                    <i data-lucide="book-open" class="w-4 h-4 inline-block mr-1"></i> View Help Center
                </a>
                <a href="{{ route('admin.docs.create') }}"
                    class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                    <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i> Add Guide
                </a>
            </div>
        </div>

        @if (session('success'))
            <div
                class="p-4  bg-gray-50 border  border-gray-200  text-gray-800 text-xs font-semibold rounded-xl flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4  text-gray-600"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-2xl border border-surface-200/70 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.docs.manage') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search title or summary..."
                        class="w-full text-xs px-3.5 py-2 rounded-xl border border-surface-200 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <select name="category"
                    class="text-xs px-3.5 py-2 rounded-xl border border-surface-200 bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Categories</option>
                    <option value="getting_started" {{ request('category') === 'getting_started' ? 'selected' : '' }}>
                        Getting Started</option>
                    <option value="orders" {{ request('category') === 'orders' ? 'selected' : '' }}>Orders</option>
                    <option value="catalog" {{ request('category') === 'catalog' ? 'selected' : '' }}>Catalog</option>
                    <option value="stores" {{ request('category') === 'stores' ? 'selected' : '' }}>Stores</option>
                    <option value="users" {{ request('category') === 'users' ? 'selected' : '' }}>Users</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 bg-surface-800 hover:bg-surface-900 text-white text-xs font-semibold rounded-xl transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-surface-200/70 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-surface-50 border-b border-surface-100 text-surface-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="p-4">Guide Title</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Visible To</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Views</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 font-medium">
                        @forelse($guides as $item)
                            <tr class="hover:bg-surface-50/50 transition">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-surface-100 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="{{ $item->icon ?: 'file-text' }}"
                                                class="w-4 h-4 text-surface-600"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.docs.show', $item->slug) }}" target="_blank"
                                                class="font-bold text-surface-900 hover:text-brand-600 transition block">
                                                {{ $item->title }}
                                            </a>
                                            <span
                                                class="text-[11px] text-surface-400 font-normal">/docs/{{ $item->slug }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 capitalize text-surface-700">
                                    <span
                                        class="px-2 py-1 rounded-md bg-surface-100 text-surface-700 border border-surface-200 text-[11px]">
                                        {{ str_replace('_', ' ', $item->category) }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($item->visible_roles ?? ['admin', 'store_admin', 'staff'] as $r)
                                            <span
                                                class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-brand-50 text-brand-700 border border-brand-200/50">
                                                {{ $r }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="p-4">
                                    <form method="POST" action="{{ route('admin.docs.toggle', $item->slug) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition {{ $item->is_published ? ' bg-gray-50  text-gray-700 border  border-gray-200 hover: bg-gray-100' : 'bg-surface-100 text-surface-500 border border-surface-200 hover:bg-surface-200' }}">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full {{ $item->is_published ? ' bg-gray-500' : 'bg-surface-400' }}"></span>
                                            {{ $item->is_published ? 'Published' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-surface-500">
                                    {{ number_format($item->views_count) }}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.docs.edit', $item->slug) }}"
                                            class="p-1.5 text-surface-500 hover:text-brand-600 hover:bg-surface-100 rounded-lg transition"
                                            title="Edit Guide">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.docs.destroy', $item->slug) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this guide?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-surface-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Delete Guide">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-surface-400">
                                    No documentation guides found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($guides->hasPages())
                <div class="p-4 border-t border-surface-100">
                    {{ $guides->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
