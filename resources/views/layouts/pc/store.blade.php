@php
    $activeStore = session()->has('active_store_id')
        ? \App\Models\Store::find(session('active_store_id'))
        : null;
@endphp

<div class="relative flex items-center shrink-0 group">  
    @if($activeStore)
        {{-- Store IS SET: Icon + Green Dot --}}
        <a href="{{ route('flow-pc.find-store') }}"
            class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-100/90 hover:bg-emerald-50 text-slate-800 hover:text-emerald-700 transition-all border border-slate-200/90 hover:border-emerald-300 cursor-pointer active:scale-95 shadow-2xs relative">
            <i data-lucide="store" class="w-4.5 h-4.5 text-slate-700"></i>
            {{-- Green Dot --}}
            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
        </a>

        {{-- Tooltip (Bottom Direction) --}}
        <div class="absolute top-full right-0 sm:left-1/2 sm:-translate-x-1/2 mt-2.5 hidden group-hover:flex flex-col items-center z-50 pointer-events-none transition-all duration-200">
            <!-- Tooltip Arrow -->
            <div class="w-2.5 h-2.5 bg-slate-900 rotate-45 -mb-1 shadow-xs"></div>
            <!-- Tooltip Content -->
            <div class="bg-slate-900 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xl whitespace-nowrap flex items-center gap-2 border border-slate-800">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>{{ $activeStore->store_name }}</span>
                <span class="text-[10px] text-slate-400 font-normal border-l border-slate-700 pl-2">Click to change</span>
            </div>
        </div>
    @else
        {{-- Store IS NOT SET: Icon + Red Dot --}}
        <a href="{{ route('flow-pc.find-store') }}"
            class="h-11 w-11 flex items-center justify-center rounded-xl bg-rose-50/80 hover:bg-rose-100 text-rose-700 transition-all border border-rose-200/90 cursor-pointer active:scale-95 shadow-2xs relative">
            <i data-lucide="map-pin" class="w-4.5 h-4.5 text-rose-600"></i>
            {{-- Red Dot --}}
            <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
        </a>

        {{-- Tooltip (Bottom Direction) --}}
        <div class="absolute top-full right-0 sm:left-1/2 sm:-translate-x-1/2 mt-2.5 hidden group-hover:flex flex-col items-center z-50 pointer-events-none transition-all duration-200">
            <!-- Tooltip Arrow -->
            <div class="w-2.5 h-2.5 bg-slate-900 rotate-45 -mb-1 shadow-xs"></div>
            <!-- Tooltip Content -->
            <div class="bg-slate-900 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-xl whitespace-nowrap flex items-center gap-2 border border-slate-800">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Select Store Location</span>
            </div>
        </div>
    @endif
</div>