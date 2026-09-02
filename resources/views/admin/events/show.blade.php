@extends(request()->is('store*') ? 'layouts.store' : 'layouts.admin')
@section('title', $event->title)

@section('content')
    @php
        $rPrefix = request()->is('store*') ? 'storepanel_cat.' : 'admin.';
    @endphp
    <div class="max-w-2xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-display font-bold text-2xl text-surface-900">{{ $event->title }}</h1>
                <p class="text-sm text-surface-500">Created {{ $event->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route($rPrefix . 'events.edit', $event) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm font-semibold rounded-xl hover:bg-brand-700 transition shadow-lg shadow-brand-200">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                </a>
                <form action="{{ route($rPrefix . 'events.destroy', $event) }}" method="POST"
                    onsubmit="return confirm('Delete this event?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-surface-100 shadow-card p-6 space-y-5">
            <div>
                <h2 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Status</h2>
                <span
                    class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-md {{ $event->is_active ? 'bg-accent-100 text-accent-700' : 'bg-surface-200 text-surface-500' }}">
                    {{ $event->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <h2 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Start Date</h2>
                    <p class="text-sm text-surface-700">
                        {{ $event->start_date ? $event->start_date->format('M d, Y') : '-' }}</p>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">End Date</h2>
                    <p class="text-sm text-surface-700">{{ $event->end_date ? $event->end_date->format('M d, Y') : '-' }}
                    </p>
                </div>
            </div>

            <div>
                <h2 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Categories</h2>
                @if ($event->categories->count())
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($event->categories as $category)
                            <span
                                class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-brand-50 text-brand-700 border border-brand-200/60">{{ $category->name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-surface-400">No categories assigned.</p>
                @endif
            </div>

            <div>
                <h2 class="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Stores</h2>
                @if ($event->stores->count())
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($event->stores as $store)
                            <span
                                class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md  bg-gray-50  text-gray-700">{{ $store->store_name }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-surface-400">No stores assigned.</p>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route($rPrefix . 'events.index') }}"
                class="text-sm text-surface-500 hover:text-brand-600 transition">&larr; Back to Events</a>
        </div>
    </div>
@endsection
