{{-- ── HOW TO USE TOOL GUIDE / INSTRUCTION SECTION ── --}}
<section class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-10 mt-6 mb-12">
    <div class="bg-white/80 backdrop-blur-xl border border-slate-200/80 rounded-3xl p-6 sm:p-8 lg:p-10 shadow-xl shadow-slate-200/40">
        
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-slate-100">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shadow-xs shrink-0">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 text-indigo-600 text-[11px] font-black uppercase tracking-wider mb-0.5">
                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i> Studio Guide
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">How to Customize Your Design</h2>
                </div>
            </div>
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold self-start sm:self-center">
                <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i> Easy 5-Step Process
            </span>
        </div>

        {{-- 5 Step Grid Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6">
            
            {{-- Step 1 --}}
            <div class="group relative bg-slate-50/70 hover:bg-white border border-slate-200/70 hover:border-pink-300 rounded-2xl p-5 shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-7 h-7 rounded-xl bg-pink-100 text-pink-600 text-xs font-black flex items-center justify-center shadow-xs">01</span>
                        <div class="w-10 h-10 rounded-2xl bg-white text-pink-500 shadow-sm border border-slate-200/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="image-plus" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-pink-600 transition-colors mb-1.5">Upload Photo</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Click <span class="font-bold text-slate-700">Photo</span> on the right dock to add your high-resolution images.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-pink-600">
                    <span>Right Studio Dock</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="group relative bg-slate-50/70 hover:bg-white border border-slate-200/70 hover:border-purple-300 rounded-2xl p-5 shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-7 h-7 rounded-xl bg-purple-100 text-purple-600 text-xs font-black flex items-center justify-center shadow-xs">02</span>
                        <div class="w-10 h-10 rounded-2xl bg-white text-purple-500 shadow-sm border border-slate-200/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="type" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-purple-600 transition-colors mb-1.5">Add Text & Style</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Use <span class="font-bold text-slate-700">+ Text</span>, <span class="font-bold text-slate-700">Color</span>, and <span class="font-bold text-slate-700">Fonts</span> to customize your message.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-purple-600">
                    <span>Typography Studio</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="group relative bg-slate-50/70 hover:bg-white border border-slate-200/70 hover:border-emerald-300 rounded-2xl p-5 shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-600 text-xs font-black flex items-center justify-center shadow-xs">03</span>
                        <div class="w-10 h-10 rounded-2xl bg-white text-emerald-500 shadow-sm border border-slate-200/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-emerald-600 transition-colors mb-1.5">Manage Layers</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Click <span class="font-bold text-slate-700">Layers</span> on the left dock to reorder or lock object stacking.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-emerald-600">
                    <span>Left Studio Dock</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="group relative bg-slate-50/70 hover:bg-white border border-slate-200/70 hover:border-indigo-300 rounded-2xl p-5 shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-7 h-7 rounded-xl bg-indigo-100 text-indigo-600 text-xs font-black flex items-center justify-center shadow-xs">04</span>
                        <div class="w-10 h-10 rounded-2xl bg-white text-indigo-500 shadow-sm border border-slate-200/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="layout-template" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors mb-1.5">Templates & Pages</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Load pre-built templates and switch canvas pages easily from left dock.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-indigo-600">
                    <span>Page Selector</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="group relative bg-slate-50/70 hover:bg-white border border-slate-200/70 hover:border-pink-400 rounded-2xl p-5 shadow-2xs hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-7 h-7 rounded-xl bg-pink-100 text-pink-600 text-xs font-black flex items-center justify-center shadow-xs">05</span>
                        <div class="w-10 h-10 rounded-2xl bg-white text-pink-600 shadow-sm border border-slate-200/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-pink-600"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 group-hover:text-pink-600 transition-colors mb-1.5">Save & Checkout</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">When done, click <span class="font-bold text-slate-700">Add to cart</span> to finalize your custom print order.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-1.5 text-[11px] font-bold text-pink-600">
                    <span>Complete Order</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </div>
            </div>

        </div>
    </div>
</section>
