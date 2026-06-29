<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title', 'Create Your Custom Print')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Dancing+Script:wght@700&family=Playfair+Display:ital,wght@0,700;1,700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-tap-highlight-color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
    @stack('styles')
</head>

<body class="antialiased select-none ">
    <div x-data="{ mobileMenu: false }" class="max-w-md mx-auto min-h-screen flex flex-col bg-slate-100 shadow-2xl relative">

        <!-- Full-Screen Modal Menu -->
        <div x-show="mobileMenu"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="absolute inset-0 z-[100] bg-white flex flex-col"
            style="display: none;"
            x-cloak>

            @php
            $activeStore = session()->has('active_store_id') ? \App\Models\Store::find(session('active_store_id')) : null;
            @endphp

            <div class="flex flex-col h-full ">
                <!-- Modal Header (Blue Theme) -->
                <div class="bg-brand-600 px-6 py-8 rounded-b-[40px] shadow-lg shadow-brand-900/10 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 bg-white rounded-2xl flex items-center justify-center shadow-md">
                                <span class="text-brand-600 font-black text-2xl">Q</span>
                            </div>
                            <span class="text-xl font-black tracking-tighter text-white uppercase">Menu</span>
                        </div>
                        <button @click="mobileMenu = false" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white/20 transition-all active:scale-90 border border-white/10">
                            <i data-lucide="x" class="w-7 h-7"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Scrollable Content -->
                <div class="flex-1 px-6 pb-8 space-y-6 overflow-y-auto">
                    <!-- Main Call to Action -->
                    <a href="{{ route('flow.index') }}" @click="mobileMenu = false" class="block w-full group relative overflow-hidden bg-brand-600 p-6 rounded-[32px] shadow-lg shadow-brand-100 transition-all hover:bg-brand-700 active:scale-[0.98]">
                        <div class="relative z-10 flex items-center justify-between">
                            <div class="flex flex-col text-left">
                                <span class="text-[10px] font-black text-brand-200 uppercase tracking-[0.2em] mb-1">Start New Flow</span>
                                <span class="text-xl font-black text-white leading-tight">Create Custom Print</span>
                            </div>
                            <div class="w-12 h-12 flex-shrink-0 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white group-hover:rotate-12 transition-transform">
                                <i data-lucide="plus" class="w-7 h-7"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Direct Print CTA -->
                    <a href="{{ route('flow.qrinto') }}" @click="mobileMenu = false" class="block w-full group relative overflow-hidden bg-white p-6 rounded-[32px] shadow-sm border border-slate-100 transition-all hover:border-brand-500 active:scale-[0.98]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-2xl text-slate-400 group-hover:text-brand-600 group-hover:bg-brand-50 transition-all">
                                    <i data-lucide="upload" class="w-6 h-6"></i>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Already have a design?</span>
                                    <span class="text-lg font-black text-slate-900 leading-tight">Custom Print</span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 group-hover:bg-brand-600 group-hover:text-white group-hover:border-brand-600 transition-all">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Store Context -->
                    <div class="bg-white p-6 rounded-[32px] shadow-sm border border-slate-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1.5 h-4 bg-brand-500 rounded-full"></div>
                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Active Branch</span>
                        </div>

                        @if($activeStore)
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 mb-4 group transition-all hover:bg-white hover:border-brand-200">
                            <div class="w-14 h-14 bg-white rounded-xl shadow-sm flex items-center justify-center text-brand-600 group-hover:bg-brand-50 transition-colors">
                                <i data-lucide="store" class="w-7 h-7"></i>
                            </div>
                            <div class="flex flex-col flex-1 overflow-hidden">
                                <span class="text-lg font-black text-slate-900 truncate tracking-tight">{{ $activeStore->store_name }}</span>
                                <div class="flex flex-col gap-0.5 mt-0.5">
                                    <span class="text-[9px] font-bold text-slate-400 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-2.5 h-2.5"></i>
                                        {{ $activeStore->city }}, {{ $activeStore->state }}
                                    </span>
                                    @if($activeStore->phone)
                                    <span class="text-[9px] font-bold text-slate-400 flex items-center gap-1">
                                        <i data-lucide="phone" class="w-2.5 h-2.5"></i>
                                        {{ $activeStore->phone }}
                                    </span>
                                    @endif
                                </div>
                                <span class="text-[9px] font-black text-green-500 flex items-center gap-1.5 uppercase mt-2">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-sm"></span>
                                    Connected
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('flow.find-store') }}" class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl bg-brand-600 text-white font-black text-sm uppercase tracking-widest hover:bg-brand-700 transition-all shadow-lg shadow-brand-100 active:scale-[0.98]">
                            Change Location
                            <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        </a>
                        @else
                        <a href="{{ route('flow.find-store') }}" class="flex items-center gap-4 p-5 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 hover:bg-amber-100 transition-colors">
                            <i data-lucide="alert-octagon" class="w-7 h-7"></i>
                            <div class="flex flex-col">
                                <span class="font-black text-sm uppercase tracking-tight">No Store Selected</span>
                                <span class="text-[10px] font-bold opacity-80">Link to a branch to begin the flow</span>
                            </div>
                        </a>
                        @endif
                    </div>

                    <!-- Tracking Quick Link -->
                    <a href="{{ route('flow.track.form') }}" class="block bg-white rounded-[28px] shadow-sm border border-slate-100 transition-all hover:border-brand-200 group overflow-hidden">
                        <div class="flex items-center justify-between p-5">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-2xl text-slate-400 group-hover:text-brand-600 group-hover:bg-brand-50 transition-all">
                                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-black text-slate-900 uppercase text-xs tracking-widest">Track My Order</span>
                                    <span class="text-[10px] font-bold text-slate-400">Real-time status check</span>
                                </div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white group-hover:border-brand-600 transition-all">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Modal Footer -->
                <div class="mt-auto pt-8 border-t border-slate-100 flex flex-col items-center gap-5">
                    <div class="flex gap-8">
                        <a href="#" class="text-slate-300 hover:text-brand-600 transition-colors"><i data-lucide="instagram" class="w-6 h-6"></i></a>
                        <a href="#" class="text-slate-300 hover:text-brand-600 transition-colors"><i data-lucide="mail" class="w-6 h-6"></i></a>
                    </div>
                    <div class="flex flex-col items-center">
                        <p class="text-[10px] font-black text-slate-200 uppercase tracking-[0.4em] mb-1">Qrinto Custom Studio</p>
                        <div class="w-10 h-1 bg-slate-50 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simplified Modern Header (Blue Theme) -->
        <header class="sticky top-0 z-50 bg-brand-600 border-b border-brand-700/50 px-5 py-4 shadow-lg shadow-brand-900/10">
            <div class="flex items-center justify-between">
                <!-- Branding -->
                <a href="{{url('/')}}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 bg-white rounded-[14px] flex items-center justify-center shadow-md group-hover:scale-105 transition-all duration-300">
                        <span class="text-brand-600 font-black text-xl leading-none">Q</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-black tracking-tight text-white leading-none">Qrinto</span>
                        <span class="text-[10px] font-extrabold text-brand-100 uppercase tracking-widest mt-0.5">Print Studio</span>
                    </div>
                </a>

                <!-- Simplified Actions -->
                <div class="flex items-center gap-3">
                    @php
                        $currentRoute = Route::currentRouteName();
                        $pcRoute = $currentRoute ? str_replace('flow.', 'flow-pc.', $currentRoute) : null;
                        $params = Route::current() ? Route::current()->parameters() : [];
                    @endphp
                    @if($pcRoute && Route::has($pcRoute))
                    <!-- <a href="{{ route($pcRoute, $params) }}" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-brand-100 hover:text-white hover:bg-white/20 transition-all border border-white/20 active:scale-95" title="Switch to PC View">
                        <i data-lucide="monitor" class="w-5 h-5"></i>
                    </a> -->
                    @endif

                    <button @click="mobileMenu = true; $nextTick(() => lucide.createIcons())" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white/20 transition-all border border-white/20 active:scale-95">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto px-6 py-4">
            @yield('content')
        </main>

        @stack('footer')
    </div>


    @stack('scripts')
</body>

</html>