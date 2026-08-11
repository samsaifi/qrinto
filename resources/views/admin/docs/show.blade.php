@extends('layouts.admin')
@section('title', $guide->title . ' - Documentation')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-surface-200 pb-4">
        <nav class="flex items-center gap-2 text-xs text-surface-500">
            <a href="{{ route('admin.docs.index') }}" class="hover:text-brand-600 font-medium transition flex items-center gap-1">
                <i data-lucide="book-open" class="w-3.5 h-3.5"></i> Documentations
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-surface-300"></i>
            <span class="font-semibold text-surface-700 capitalize">{{ str_replace('_', ' ', $guide->category) }}</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-surface-300"></i>
            <span class="text-surface-900 font-bold truncate max-w-[200px] sm:max-w-xs">{{ $guide->title }}</span>
        </nav>

        @if(auth()->user()->isAdmin())
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.docs.edit', $guide->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface-100 hover:bg-surface-200 text-surface-800 text-xs font-semibold rounded-lg border border-surface-200 transition">
                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit Guide
            </a>
        </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation Menu for Category -->
        <aside class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl border border-surface-200/70 p-4 shadow-2xs space-y-3 sticky top-6">
                <h3 class="font-display font-bold text-sm text-surface-900 border-b border-surface-100 pb-2.5 flex items-center gap-2 capitalize">
                    <i data-lucide="list" class="w-4 h-4 text-brand-600"></i> {{ str_replace('_', ' ', $guide->category) }} Guides
                </h3>
                <div class="space-y-1">
                    @foreach($categoryGuides as $item)
                    <a href="{{ route('admin.docs.show', $item->slug) }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition {{ $item->id === $guide->id ? 'bg-brand-50 text-brand-700 font-bold border border-brand-200/60' : 'text-surface-600 hover:bg-surface-50 hover:text-surface-900' }}">
                        <i data-lucide="{{ $item->icon ?: 'file-text' }}" class="w-4 h-4 flex-shrink-0 {{ $item->id === $guide->id ? 'text-brand-600' : 'text-surface-400' }}"></i>
                        <span class="truncate">{{ $item->title }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- Main Content Article -->
        <main class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl border border-surface-200/70 p-6 sm:p-8 shadow-xs space-y-6">
                <!-- Header -->
                <div class="border-b border-surface-100 pb-6 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-brand-50 border border-brand-200/60 text-brand-700 text-xs font-semibold rounded-full capitalize">
                        <i data-lucide="{{ $guide->icon }}" class="w-3.5 h-3.5"></i> {{ str_replace('_', ' ', $guide->category) }}
                    </div>
                    <h1 class="font-display text-2xl sm:text-3xl font-extrabold text-surface-900 tracking-tight">{{ $guide->title }}</h1>
                    @if($guide->summary)
                    <p class="text-surface-600 text-sm leading-relaxed bg-surface-50 p-3.5 rounded-xl border border-surface-100">{{ $guide->summary }}</p>
                    @endif
                </div>

                <!-- Body HTML Content -->
                <div class="prose prose-brand max-w-none text-surface-700 text-sm leading-relaxed">
                    {!! $guide->content !!}
                </div>

                <!-- Footer Action Tip -->
                <div class="pt-6 border-t border-surface-100 flex items-center justify-between text-xs text-surface-500">
                    <span>Was this guide helpful? If you need further assistance, ask your Store Administrator.</span>
                </div>
            </div>

            <!-- Previous / Next Guide Buttons -->
            <div class="grid sm:grid-cols-2 gap-4">
                @if($previousGuide)
                <a href="{{ route('admin.docs.show', $previousGuide->slug) }}" class="p-4 bg-white rounded-xl border border-surface-200/70 hover:border-brand-300 hover:shadow-xs transition group">
                    <span class="text-xs font-medium text-surface-400 block mb-1 flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-1 transition"></i> Previous Guide
                    </span>
                    <span class="text-sm font-bold text-surface-800 group-hover:text-brand-600 transition block truncate">{{ $previousGuide->title }}</span>
                </a>
                @else
                <div></div>
                @endif

                @if($nextGuide)
                <a href="{{ route('admin.docs.show', $nextGuide->slug) }}" class="p-4 bg-white rounded-xl border border-surface-200/70 hover:border-brand-300 hover:shadow-xs transition text-right group">
                    <span class="text-xs font-medium text-surface-400 block mb-1 flex items-center justify-end gap-1">
                        Next Guide <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition"></i>
                    </span>
                    <span class="text-sm font-bold text-surface-800 group-hover:text-brand-600 transition block truncate">{{ $nextGuide->title }}</span>
                </a>
                @endif
            </div>

            <!-- Related Guides Section -->
            @if(count($relatedGuides))
            <div class="bg-white rounded-2xl border border-surface-200/70 p-6 shadow-xs space-y-4">
                <h3 class="font-display font-bold text-sm text-surface-900">Related Guides</h3>
                <div class="grid sm:grid-cols-3 gap-3">
                    @foreach($relatedGuides as $rel)
                    <a href="{{ route('admin.docs.show', $rel->slug) }}" class="p-3 bg-surface-50 hover:bg-brand-50/40 rounded-xl border border-surface-100 hover:border-brand-200 transition group">
                        <h4 class="font-bold text-xs text-surface-800 group-hover:text-brand-700 truncate mb-1">{{ $rel->title }}</h4>
                        <p class="text-[11px] text-surface-500 line-clamp-2 leading-tight">{{ $rel->summary }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </main>
    </div>
</div>
@endsection
