@php
    $activeStore = session()->has('active_store_id')
        ? \App\Models\Store::find(session('active_store_id'))
        : null;
@endphp

<div class="hidden xl:flex items-center shrink-0">  
    @if($activeStore)
        <a href="{{ route('flow-pc.find-store') }}"
            title="Change Store Location"
            class="flex items-center gap-2 bg-slate-100/80 hover:bg-brand-50 border border-slate-200/80 hover:border-brand-200 rounded-xl px-3 py-2 transition-all text-xs font-bold text-slate-700 hover:text-brand-600 group">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
            <span class="truncate max-w-[130px]">{{ $activeStore->store_name }}</span>
            <i data-lucide="arrow-right-left" class="w-3 h-3 text-slate-400 group-hover:text-brand-600 transition-colors shrink-0"></i>
        </a>
    @else
        <a href="{{ route('flow-pc.find-store') }}"
            class="flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 rounded-xl px-3 py-2 transition-all text-amber-800 font-bold text-xs">
            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600 shrink-0"></i>
            <span>Select Store</span>
        </a>
    @endif
</div>