@extends('layouts.admin')
@section('title', 'Documentations & User Guides')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header & Search Hero -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-surface-900 via-surface-850 to-brand-950 p-8 text-white shadow-xl border border-surface-800">
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/20 border border-brand-400/30 text-brand-300 text-xs font-semibold uppercase tracking-wider mb-4">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i> Employee Help Center
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-extrabold tracking-tight text-white mb-3">How can we help you today?</h1>
            <p class="text-surface-300 text-sm sm:text-base mb-6 leading-relaxed">Search user guides, step-by-step instructions, and common troubleshooting tips for managing your store.</p>

            <form method="GET" action="{{ route('admin.docs.index') }}" class="flex items-center gap-3 bg-surface-900/90 border border-surface-700/80 p-1.5 rounded-2xl shadow-inner focus-within:ring-2 focus-within:ring-brand-500 transition">
                <div class="flex-1 flex items-center gap-3 px-3">
                    <i data-lucide="search" class="w-5 h-5 text-surface-400 flex-shrink-0"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search guides e.g. 'How to process an order', 'Add product'..." class="w-full bg-transparent border-0 text-white placeholder-surface-400 text-sm focus:outline-none focus:ring-0">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition shadow-md">
                    Search
                </button>
            </form>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none">
            <i data-lucide="help-circle" class="w-80 h-80 text-white"></i>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-surface-900">Documentation System</h2>
            <p class="text-xs text-surface-500">Access documentation guides or manage guide content.</p>
        </div>
        <a href="{{ route('admin.docs.manage') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-surface-100 hover:bg-surface-200 border border-surface-200 text-surface-800 text-xs font-semibold rounded-xl transition">
            <i data-lucide="settings" class="w-4 h-4"></i> Manage Guides (CMS)
        </a>
    </div>
    @endif

    <!-- Category Section Cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($groupedCategories as $key => $cat)
        <div class="bg-white rounded-2xl border border-surface-200/70 shadow-xs hover:shadow-md transition duration-200 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-surface-100 flex-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-6 h-6"></i>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-surface-100 text-surface-600">
                        {{ count($cat['guides']) }} {{ Str::plural('guide', count($cat['guides'])) }}
                    </span>
                </div>
                <h3 class="font-display font-bold text-lg text-surface-900 mb-1.5">{{ $cat['title'] }}</h3>
                <p class="text-xs text-surface-500 leading-relaxed mb-4">{{ $cat['description'] }}</p>

                <!-- List of guides in this category -->
                <div class="space-y-2">
                    @forelse($cat['guides']->take(4) as $guide)
                    <a href="{{ route('admin.docs.show', $guide->slug) }}" class="group flex items-center justify-between p-2 rounded-lg hover:bg-surface-50 transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i data-lucide="{{ $guide->icon ?: 'file-text' }}" class="w-4 h-4 text-surface-400 group-hover:text-brand-600 transition flex-shrink-0"></i>
                            <span class="text-xs font-semibold text-surface-700 group-hover:text-brand-600 truncate">{{ $guide->title }}</span>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-surface-300 group-hover:text-brand-600 group-hover:translate-x-0.5 transition flex-shrink-0"></i>
                    </a>
                    @empty
                    <p class="text-xs text-surface-400 italic">No guides available in this section.</p>
                    @endforelse
                </div>
            </div>

            @if(count($cat['guides']) > 4)
            <div class="bg-surface-50 px-6 py-3 border-t border-surface-100 text-right">
                <a href="{{ route('admin.docs.show', $cat['guides']->first()->slug) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                    View all {{ count($cat['guides']) }} guides <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Quick Guides & Popular Articles -->
    @if(count($popularGuides))
    <div class="bg-white rounded-2xl border border-surface-200/70 p-6 shadow-xs">
        <h3 class="font-display font-bold text-lg text-surface-900 mb-4 flex items-center gap-2">
            <i data-lucide="sparkles" class="w-5 h-5 text-amber-500"></i> Popular Quick Guides
        </h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($popularGuides as $popular)
            <a href="{{ route('admin.docs.show', $popular->slug) }}" class="p-4 rounded-xl border border-surface-100 hover:border-brand-300 hover:bg-brand-50/30 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-brand-600 mb-2">
                        <i data-lucide="{{ $popular->icon }}" class="w-4 h-4"></i>
                        <span>{{ ucfirst(str_replace('_', ' ', $popular->category)) }}</span>
                    </div>
                    <h4 class="font-bold text-surface-800 group-hover:text-brand-700 transition text-sm mb-1.5">{{ $popular->title }}</h4>
                    <p class="text-xs text-surface-500 line-clamp-2">{{ $popular->summary }}</p>
                </div>
                <div class="mt-4 flex items-center text-xs font-semibold text-brand-600 group-hover:translate-x-1 transition">
                    Read guide <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
