@extends('layouts.admin')
@section('title', 'Events')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-display font-bold text-2xl text-surface-900">Events</h1>
            <p class="text-sm text-surface-500">Manage your events</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
            <i data-lucide="plus" class="w-4 h-4"></i> Add Event
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-5 mb-6">
        <form action="{{ route('admin.events.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Event title..."
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-surface-500 mb-1">Status</label>
                <select name="status"
                    class="rounded-xl border-surface-200 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition">Filter</button>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-surface-500 hover:text-brand-600">Clear</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-surface-100 shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="bg-surface-50 border-b border-surface-100">
                        <th
                            class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-16">
                            Icon</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider">
                            Title</th>
                        <th
                            class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-20">
                            Status</th>
                        @if (auth()->user()->isAdmin())
                            <th
                                class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">
                                Stores</th>
                        @endif
                        <th
                            class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden md:table-cell">
                            Created By</th>
                        <th
                            class="px-3 py-3 text-left text-xs font-semibold text-surface-500 uppercase tracking-wider hidden sm:table-cell w-32">
                            Created</th>
                        <th
                            class="px-3 py-3 text-center text-xs font-semibold text-surface-500 uppercase tracking-wider w-28">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @forelse($events as $event)
                        <tr class="hover:bg-surface-50/60 transition-colors">
                            <td class="px-3 py-3 text-center">
                                @if ($event->icon_svg)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg"
                                        style="background-color: {{ $event->color ?? '#000000' }}20; color: {{ $event->color ?? '#000000' }}">
                                        {!! $event->icon($event->icon_svg) !!}

                                    </span>
                                @elseif($event->color)
                                    <span class="inline-block w-5 h-5 rounded-full border border-surface-200"
                                        style="background-color: {{ $event->color }}"></span>
                                @else
                                    <span class="text-xs text-surface-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.events.show', $event) }}"
                                    class="font-semibold text-sm text-surface-800 hover:text-brand-600 transition">{{ $event->title }}</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-md {{ $event->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            @if (auth()->user()->isAdmin())
                                <td class="px-3 py-3 hidden md:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($event->stores as $store)
                                            <span
                                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md  bg-gray-50  text-gray-700">{{ $store->store_name }}</span>
                                        @empty
                                            <span class="text-xs text-surface-400">&mdash;</span>
                                        @endforelse
                                    </div>
                                </td>
                            @endif
                            <td class="px-3 py-3 hidden md:table-cell">
                                @if ($event->creator)
                                    <span
                                        class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md {{ $event->creator->isAdmin() ? 'bg-brand-50 text-brand-700 border border-brand-200/60' : 'bg-amber-50 text-amber-700' }}">
                                        {{ $event->creator->isAdmin() ? 'Admin' : 'Store Admin' }}
                                    </span>
                                    <span class="block text-xs text-surface-400 mt-0.5">{{ $event->creator->name }}</span>
                                @else
                                    <span class="text-xs text-surface-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 hidden sm:table-cell">
                                <span class="text-sm text-surface-500">{{ $event->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.events.show', $event) }}"
                                        class="inline-flex p-1.5 rounded-lg hover:bg-surface-100 text-surface-400 hover:text-surface-600 transition"
                                        title="View">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}"
                                        class="inline-flex p-1.5 rounded-lg hover:bg-brand-50 text-surface-400 hover:text-brand-600 transition"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                        onsubmit="return confirm('Delete this event?')">
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
                            <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}"
                                class="px-6 py-12 text-center text-surface-400">No events yet. <a
                                    href="{{ route('admin.events.create') }}" class="text-brand-600 font-medium">Add your
                                    first event</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($events->hasPages())
            <div class="px-6 py-4 border-t border-surface-100">{{ $events->links() }}</div>
        @endif
    </div>
@endsection
