@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', 'Product Types & Sizes')

@section('content')
@php
    $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
@endphp
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="font-display font-bold text-2xl text-surface-900">Product Types & Sizes</h1>
        <p class="text-sm text-surface-500">Manage product categories and their available sizes</p>
    </div>
    <a href="{{ route($rPrefix . 'product-types.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Type/Size
    </a>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
    <form action="{{ route($rPrefix . 'product-types.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Type name..."
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
        <a href="{{ route($rPrefix . 'product-types.index') }}" class="text-sm text-surface-500 hover:text-brand-600">Clear</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px]">
            <thead>
                <tr class="bg-surface-50 border-b border-surface-100">
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-16">Icon</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">Name</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20">Status</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell w-24">Price</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden lg:table-cell w-32">Dimensions</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden sm:table-cell w-32">Created</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-50">
                @forelse($productTypes as $type)
                {{-- Parent row --}}
                <tr class="bg-surface-50/40 hover:bg-surface-50 transition-colors cursor-pointer"
                    onclick="document.getElementById('children-{{ $type->id }}').classList.toggle('hidden'); this.querySelector('.chevron-icon').classList.toggle('rotate-90')">
                    <td class="px-3 py-3 text-center">
                        @if($type->icon_svg)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600">{!! $type->icon_svg !!}</span>
                        @else
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-100 text-surface-400"><i data-lucide="tag" class="w-4 h-4"></i></span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            @if($type->children->count())
                                <i data-lucide="chevron-right" class="w-4 h-4 text-surface-400 chevron-icon transition-transform duration-200"></i>
                            @endif
                            <div>
                                <span class="font-bold text-sm text-surface-900">{{ $type->name }}</span>
                                @if($type->title)
                                    <p class="text-xs text-surface-400 italic">{{ $type->title }}</p>
                                @endif
                                @if($type->children->count())
                                    <p class="text-xs text-surface-400">{{ $type->children->count() }} {{ Str::plural('size', $type->children->count()) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $type->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                            {{ $type->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-right hidden md:table-cell">
                        @if($type->price)
                            <span class="text-sm font-bold text-brand-600">{{ \App\Services\CurrencyService::format($type->price) }}</span>
                        @else
                            <span class="text-xs text-surface-400">&mdash;</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 hidden lg:table-cell">
                        <span class="text-xs text-surface-400">&mdash;</span>
                    </td>
                    <td class="px-3 py-3 hidden sm:table-cell">
                        <span class="text-sm text-surface-500">{{ $type->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="px-3 py-3 text-center" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route($rPrefix . 'product-types.edit', $type) }}" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route($rPrefix . 'product-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this type?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                {{-- Child rows (hidden by default) --}}
                @if($type->children->count())
                <tr id="children-{{ $type->id }}" class="hidden">
                    <td colspan="7" class="p-0">
                        <table class="w-full">
                            <tbody class="divide-y divide-surface-50">
                                @foreach($type->children as $child)
                                <tr class="hover:bg-brand-50/30 transition-colors bg-white">
                                    <td class="px-3 py-2.5 text-center w-16">
                                        @if($child->icon_svg)
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded text-surface-500">{!! $child->icon_svg !!}</span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded text-surface-300"><i data-lucide="maximize" class="w-3.5 h-3.5"></i></span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-2.5">
                                        <div class="flex items-center gap-2 pl-6">
                                            <span class="text-surface-300">└</span>
                                            <div>
                                                <span class="font-medium text-sm text-surface-700">{{ $child->name }}</span>
                                                @if($child->title)
                                                    <span class="text-xs text-surface-400 ml-1">({{ $child->title }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center w-20">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $child->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-right hidden md:table-cell w-24">
                                        @if($child->price)
                                            <span class="text-sm font-bold text-brand-600">{{ \App\Services\CurrencyService::format($child->price) }}</span>
                                            @if($child->old_price)
                                                <span class="block text-xs text-surface-400 line-through">{{ \App\Services\CurrencyService::format($child->old_price) }}</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-surface-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 hidden lg:table-cell w-32">
                                        @if($child->width && $child->height)
                                            <span class="text-sm text-surface-600">{{ $child->width }}x{{ $child->height }} {{ $child->unit }}</span>
                                        @else
                                            <span class="text-xs text-surface-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 hidden sm:table-cell w-32">
                                        <span class="text-sm text-surface-500">{{ $child->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-3 py-2.5 text-center w-28">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route($rPrefix . 'product-types.edit', $child) }}" class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition" title="Edit">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </a>
                                            <form action="{{ route($rPrefix . 'product-types.destroy', $child) }}" method="POST" onsubmit="return confirm('Delete this size?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex p-1.5 rounded-lg hover:bg-red-50 text-surface-400 hover:text-red-600 transition" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-surface-400">No product types yet. <a href="{{ route($rPrefix . 'product-types.create') }}" class="text-brand-600 font-medium">Create your first one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($productTypes->hasPages())
    <div class="px-6 py-4 border-t border-surface-100">{{ $productTypes->links() }}</div>
    @endif
</div>
@endsection
