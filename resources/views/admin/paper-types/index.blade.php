@extends('layouts.admin')
@section('title', 'Paper Types')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Paper Types</h1>
        <p class="text-sm text-surface-500">Manage your paper types</p>
    </div>
    <a href="{{ route('admin.paper-types.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Paper Type
    </a>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
    <form action="{{ route('admin.paper-types.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Paper type title..."
                   class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
            <select name="status" class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Filter</button>
        <a href="{{ route('admin.paper-types.index') }}" class="text-sm text-surface-500 hover:text-brand-600">Clear</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Title</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20">Status</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Stores</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">Created By</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden sm:table-cell w-32">Created</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                @forelse($paperTypes as $paperType)
                <tr class="hover:bg-surface-50/60 transition-colors">
                    <td class="px-5 py-3">
                        <span class="font-semibold text-sm text-surface-800">{{ $paperType->title }}</span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $paperType->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                            {{ $paperType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <div class="flex flex-wrap gap-1">
                            @forelse($paperType->stores as $store)
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md bg-emerald-50 text-emerald-700">{{ $store->store_name }}</span>
                            @empty
                            <span class="text-xs text-surface-400">&mdash;</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-3 py-3 hidden md:table-cell">
                        <span class="text-sm text-surface-600">{{ $paperType->user->name ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-3 hidden sm:table-cell">
                        <span class="text-sm text-surface-500">{{ $paperType->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.paper-types.edit', $paperType) }}" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.paper-types.destroy', $paperType) }}" method="POST" onsubmit="return confirm('Delete this paper type?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-surface-400">No paper types yet. <a href="{{ route('admin.paper-types.create') }}" class="text-brand-600 font-medium">Add your first paper type</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($paperTypes->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $paperTypes->links() }}</div>
    @endif
</div>
@endsection
