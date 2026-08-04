@php
    $activeStore = session()->has('active_store_id')
        ? \App\Models\Store::find(session('active_store_id'))
        : null;
@endphp

<div class="hidden lg:flex items-center shrink-0">  
    @if($activeStore)
        <a href="{{ route('flow-pc.find-store') }}"
            class="flex items-center gap-3 bg-slate-50 hover:bg-brand-50/70 border border-slate-200 hover:border-brand-300 rounded-2xl px-3.5 py-2 transition-all duration-200 group shadow-xs">
            <div class="relative flex items-center justify-center">
                <div class="w-8 h-8 rounded-xl bg-white shadow-xs border border-slate-200 flex items-center justify-center text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                    <i data-lucide="store" class="w-4 h-4"></i>
                </div>
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white animate-pulse"></span>
            </div>
            <div class="flex flex-col text-left">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none flex items-center gap-1">
                    Active Branch
                </span>
                <span class="text-xs font-extrabold text-slate-800 group-hover:text-brand-600 transition-colors truncate max-w-[160px] mt-0.5">
                    {{ $activeStore->store_name }}
                </span>
            </div>
            <i data-lucide="arrow-right-left" class="w-3.5 h-3.5 text-slate-400 group-hover:text-brand-600 transition-colors ml-1"></i>
        </a>
    @else
        <a href="{{ route('flow-pc.find-store') }}"
            class="flex items-center gap-2.5 bg-amber-50 hover:bg-amber-100/80 border border-amber-200/80 rounded-2xl px-3.5 py-2 transition-all duration-200 text-amber-800 font-bold text-xs shadow-xs">
            <i data-lucide="map-pin" class="w-4 h-4 text-amber-600"></i>
            <span>Select Store</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-amber-500"></i>
        </a>
    @endif
</div>