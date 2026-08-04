 @extends('layouts.quick-flow-pc')

 @section('title', 'What would you like to create?')
 @section('header_title', 'Create')

 @push('styles')
     <style>
         .hero-home-gradient {
             background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 30%, #faf0ff 60%, #f0f4ff 100%);
         }

         .hero-home-pattern {
             background-image: radial-gradient(circle at 1px 1px, rgba(236, 72, 153, 0.04) 1px, transparent 0);
             background-size: 32px 32px;
         }

         .hero-blob-1 {
             position: absolute;
             top: -60px;
             right: 15%;
             width: 300px;
             height: 300px;
             background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, transparent 70%);
             border-radius: 50%;
             filter: blur(40px);
         }

         .hero-blob-2 {
             position: absolute;
             bottom: -40px;
             right: 5%;
             width: 200px;
             height: 200px;
             background: radial-gradient(circle, rgba(249, 168, 212, 0.2) 0%, transparent 70%);
             border-radius: 50%;
             filter: blur(30px);
         }

         .hero-blob-3 {
             position: absolute;
             top: 20%;
             right: 35%;
             width: 80px;
             height: 80px;
             background: rgba(236, 72, 153, 0.2);
             border-radius: 50%;
             filter: blur(0px);
         }

         .hero-dots {
             position: absolute;
             top: 10%;
             right: 3%;
             width: 80px;
             height: 80px;
             background-image: radial-gradient(circle, rgba(236, 72, 153, 0.25) 2px, transparent 2px);
             background-size: 10px 10px;
             border-radius: 50%;
         }

         .hero-image-frame {
             position: relative;
             transform: perspective(800px) rotateY(-5deg) rotateX(2deg);
             transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .hero-image-frame:hover {
             transform: perspective(800px) rotateY(0deg) rotateX(0deg);
         }

         .product-card {
             transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .product-card:hover {
             transform: translateY(-6px);
             box-shadow: 0 24px 48px -16px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(236, 72, 153, 0.12);
         }

         .product-card:hover .product-icon {
             transform: scale(1.1);
         }

         .product-card:hover .product-arrow {
             opacity: 1;
             transform: translateX(0);
         }

         .product-card.disabled-card {
             opacity: 0.45;
             filter: grayscale(1);
             pointer-events: none;
         }

         .product-icon {
             transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .product-icon svg {
             width: 100%;
             height: 100%;
             fill: currentColor !important;
         }

         .product-arrow {
             opacity: 0;
             transform: translateX(-8px);
             transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .step-connector {
             position: relative;
         }

         .step-connector::after {
             content: '';
             position: absolute;
             top: 50%;
             right: -1.5rem;
             width: 2rem;
             height: 2px;
             background: repeating-linear-gradient(90deg, #c7d2fe 0, #c7d2fe 4px, transparent 4px, transparent 8px);
             transform: translateY(-50%);
         }

         .step-connector:last-child::after {
             display: none;
         }

         .step-card {
             transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .step-card:hover {
             transform: translateY(-4px);
             box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.08);
         }

         .feature-card {
             transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .feature-card:hover {
             transform: translateY(-3px);
             box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.06);
         }

         .review-card-home {
             transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
         }

         .review-card-home:hover {
             transform: translateY(-2px);
             box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.06);
         }

         .fade-in {
             animation: fadeIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
         }

         .fade-in-d1 {
             animation-delay: 0.1s;
         }

         .fade-in-d2 {
             animation-delay: 0.2s;
         }

         .fade-in-d3 {
             animation-delay: 0.3s;
         }

         @keyframes fadeIn {
             from {
                 opacity: 0;
                 transform: translateY(20px);
             }

             to {
                 opacity: 1;
                 transform: translateY(0);
             }
         }

         .stat-divider {
             position: relative;
         }

         .stat-divider:not(:last-child)::after {
             content: '';
             position: absolute;
             right: 0;
             top: 50%;
             transform: translateY(-50%);
             height: 32px;
             width: 1px;
             background: #e2e8f0;
         }

         .step-connector-line {
             position: relative;
         }

         .step-connector-line::after {
             content: '···›';
             position: absolute;
             top: 50%;
             right: -1.25rem;
             transform: translateY(-50%);
             color: #cbd5e1;
             font-size: 1.25rem;
             letter-spacing: 2px;
         }

         .step-connector-line:last-child::after {
             display: none;
         }
     </style>
 @endpush

 @section('content')
     <div class="pb-24">

         {{-- ===== SECTION 1: HERO ===== --}}
         <section class="hero-home-gradient hero-home-pattern -mx-10 -mt-4 px-10 pt-16 pb-20 relative overflow-hidden">
             <div class="hero-blob-1"></div>
             <div class="hero-blob-2"></div>
             <div class="hero-blob-3"></div>
             <div class="hero-dots"></div>

             <div class="max-w-[1400px] mx-auto relative">
                 <div class="grid grid-cols-12 gap-12 items-center">
                     {{-- Left: Content --}}
                     <div class="col-span-12 lg:col-span-6 xl:col-span-5">
                         <div class="fade-in">
                             <span
                                 class="inline-flex items-center gap-2 bg-white/80 border border-brand-100 text-brand-600 text-sm font-semibold px-4 py-2 rounded-full mb-6">
                                 <i data-lucide="sparkles" class="w-4 h-4"></i>
                                 Premium print studio
                             </span>
                         </div>

                         <h1
                             class="text-5xl xl:text-6xl font-extrabold text-slate-900 leading-[1.1] tracking-tight fade-in fade-in-d1">
                             Turn your memories<br>
                             into <span
                                 class="bg-gradient-to-r from-brand-600 to-violet-500 bg-clip-text text-transparent italic"
                                 style="font-family: 'Playfair Display', serif;">beautiful prints</span>
                         </h1>

                         <p class="text-lg xl:text-xl text-slate-500 mt-6 leading-relaxed max-w-lg fade-in fade-in-d2">
                             From photos to canvas, cards to books &mdash; we use museum-quality materials and vibrant
                             printing to bring your moments to life.
                         </p>

                         <div class="flex items-center gap-4 mt-8 fade-in fade-in-d3">
                             <a href="#products"
                                 class="bg-brand-600 hover:bg-brand-700 text-white font-semibold px-7 py-3.5 rounded-xl transition-all duration-200 active:scale-95 flex items-center gap-2 shadow-lg shadow-brand-600/20">
                                 <i data-lucide="pen-tool" class="w-4 h-4"></i>
                                 Start Designing
                             </a>
                             <a href="{{ url('/pc/templates') }}"
                                 class="bg-white hover:bg-slate-50 text-slate-700 font-semibold px-7 py-3.5 rounded-xl border border-slate-200 transition-all duration-200 active:scale-95 flex items-center gap-2">
                                 <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                 Browse Templates
                             </a>
                         </div>

                         {{-- Stats --}}
                         <div class="mt-10 flex items-center gap-6 xl:gap-8 fade-in fade-in-d3">
                             <div class="stat-divider pr-6 xl:pr-8">
                                 <div class="flex items-center gap-2 mb-1">
                                     <i data-lucide="printer" class="w-5 h-5 text-brand-500"></i>
                                     <p class="text-2xl xl:text-3xl font-extrabold text-slate-900">2.4M+</p>
                                 </div>
                                 <p class="text-sm text-slate-500">Products printed</p>
                             </div>
                             <div class="stat-divider pr-6 xl:pr-8">
                                 <div class="flex items-center gap-2 mb-1">
                                     <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                                     <p class="text-2xl xl:text-3xl font-extrabold text-slate-900">4.9</p>
                                 </div>
                                 <p class="text-sm text-slate-500">Average rating</p>
                             </div>
                             <div class="stat-divider pr-6 xl:pr-8">
                                 <div class="flex items-center gap-2 mb-1">
                                     <i data-lucide="clock" class="w-5 h-5 text-brand-500"></i>
                                     <p class="text-2xl xl:text-3xl font-extrabold text-slate-900">48hr</p>
                                 </div>
                                 <p class="text-sm text-slate-500">Fast delivery</p>
                             </div>
                             <div class="stat-divider">
                                 <div class="flex items-center gap-2 mb-1">
                                     <i data-lucide="user-check" class="w-5 h-5 text-brand-500"></i>
                                     <p class="text-2xl xl:text-3xl font-extrabold text-slate-900">100%</p>
                                 </div>
                                 <p class="text-sm text-slate-500">Satisfaction</p>
                             </div>
                         </div>
                     </div>

                     {{-- Right: Hero Image --}}
                     <div class="col-span-12 lg:col-span-6 xl:col-span-7 hidden lg:flex justify-center">
                         <div class="relative fade-in fade-in-d2">
                             <div class="hero-image-frame">
                                 <div
                                     class="w-[480px] h-[340px] rounded-3xl overflow-hidden shadow-2xl shadow-brand-900/15 border border-white/60 bg-gradient-to-br from-brand-100 via-violet-100 to-pink-100 flex items-center justify-center">
                                     <div class="grid grid-cols-2 gap-3 p-6 w-full h-full">
                                         <div class="rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lg">
                                         </div>
                                         <div class="rounded-2xl bg-gradient-to-br from-violet-400 to-pink-400 shadow-lg">
                                         </div>
                                         <div
                                             class="rounded-2xl bg-gradient-to-br from-cyan-400 to-teal-400 shadow-lg col-span-2 h-24">
                                         </div>
                                     </div>
                                 </div>
                             </div>
                             <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-brand-200/40 rounded-full blur-2xl"></div>
                             <div class="absolute -top-6 -right-6 w-24 h-24 bg-pink-200/40 rounded-full blur-2xl"></div>
                         </div>
                     </div>
                 </div>
             </div>
         </section>

         {{-- ===== SECTION 2: SHOP BY CATEGORY ===== --}}
         <section id="products" class="max-w-[1400px] mx-auto pt-20 pb-16 scroll-mt-20">
             <div class="flex items-end justify-between mb-10">
                 <div>
                     <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">Shop by Category</h2>
                     <p class="text-lg text-slate-500 mt-2">Choose a product to start creating your custom design.</p>
                 </div>
                 <a href="{{ route('flow-pc.find-store') }}"
                     class="hidden lg:flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                     View all products <i data-lucide="arrow-right" class="w-4 h-4"></i>
                 </a>
             </div>

             <div
                 class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-{{ min(count($productTypes), 6) }} gap-6">
                 @foreach ($productTypes as $index => $type)
                     @if ($type->is_active)
                         <a href="{{ route('flow-pc.category', $type->slug) }}"
                             class="product-card bg-white border border-slate-200 rounded-2xl p-8 text-center group relative">
                             <div
                                 class="product-icon w-[72px] h-[72px] bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-5 p-4 text-brand-500 group-hover:bg-brand-100 transition-colors">
                                 @if ($type->icon_svg)
                                     {!! $type->icon_svg !!}
                                 @else
                                     <i data-lucide="package" class="w-8 h-8 text-brand-500"></i>
                                 @endif
                             </div>
                             <h3 class="font-bold text-slate-900 text-base">{{ $type->name }}</h3>
                             @if ($type->title)
                                 <p class="text-sm text-slate-400 mt-1.5">{{ $type->title }}</p>
                             @endif
                             <div
                                 class="product-arrow mt-4 flex items-center justify-center gap-1 text-sm font-semibold text-brand-600">
                                 Create now <i data-lucide="arrow-right" class="w-4 h-4"></i>
                             </div>
                         </a>
                     @else
                         <div
                             class="product-card disabled-card bg-white border border-slate-200 rounded-2xl p-8 text-center relative">
                             <span
                                 class="absolute top-4 right-4 bg-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg">Paused</span>
                             <div
                                 class="product-icon w-[72px] h-[72px] bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-5 p-4">
                                 @if ($type->icon_svg)
                                     {!! $type->icon_svg !!}
                                 @else
                                     <i data-lucide="package" class="w-8 h-8 text-slate-300"></i>
                                 @endif
                             </div>
                             <h3 class="font-bold text-slate-900 text-base">{{ $type->name }}</h3>
                             @if ($type->title)
                                 <p class="text-sm text-slate-400 mt-1.5">{{ $type->title }}</p>
                             @endif
                         </div>
                     @endif
                 @endforeach
             </div>
         </section>

         {{-- ===== SECTION 3: HOW IT WORKS ===== --}}
         <section class="max-w-[1400px] mx-auto py-16 border-t border-slate-100">
             <div class="text-center mb-12">
                 <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">How it works</h2>
                 <p class="text-lg text-slate-500 mt-2">Create stunning prints in just a few simple steps.</p>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
                 @php
                     $steps = [
                         [
                             'icon' => 'store',
                             'color' => 'brand',
                             'title' => 'Select a Store',
                             'desc' => 'Choose your nearest Qrinto print studio for local pickup or delivery.',
                         ],
                         [
                             'icon' => 'layers',
                             'color' => 'violet',
                             'title' => 'Choose a Product',
                             'desc' => 'Browse our range of premium prints, canvas, books, and gifts.',
                         ],
                         [
                             'icon' => 'image-plus',
                             'color' => 'emerald',
                             'title' => 'Upload & Design',
                             'desc' => 'Upload your photos and customize with our powerful design editor.',
                         ],
                         [
                             'icon' => 'package-check',
                             'color' => 'rose',
                             'title' => 'Order & Collect',
                             'desc' => 'Checkout securely, then pick up or get fast delivery.',
                         ],
                     ];
                 @endphp

                 @foreach ($steps as $i => $step)
                     <div class="step-connector">
                         <div class="step-card bg-white border border-slate-200 rounded-2xl p-8 relative group">
                             <div class="flex items-center gap-3 mb-5">
                                 <div
                                     class="w-14 h-14 bg-{{ $step['color'] }}-50 rounded-2xl flex items-center justify-center group-hover:bg-{{ $step['color'] }}-100 group-hover:scale-110 transition-all duration-300">
                                     <i data-lucide="{{ $step['icon'] }}"
                                         class="w-7 h-7 text-{{ $step['color'] }}-600"></i>
                                 </div>
                                 <span
                                     class="text-xs font-bold text-{{ $step['color'] }}-600 uppercase tracking-wider bg-{{ $step['color'] }}-50 px-2.5 py-1 rounded-lg">Step
                                     {{ $i + 1 }}</span>
                             </div>
                             <h3 class="font-bold text-slate-900 text-lg mb-2">{{ $step['title'] }}</h3>
                             <p class="text-sm text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                         </div>
                     </div>
                 @endforeach
             </div>
         </section>

         {{-- ===== SECTION 4: WHY CHOOSE US ===== --}}
         <section class="max-w-[1400px] mx-auto py-16 border-t border-slate-100">
             <div class="grid grid-cols-12 gap-10 items-center">
                 <div class="col-span-12 lg:col-span-5">
                     <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">Why choose<br>Qrinto
                         Print Studio?</h2>
                     <p class="text-lg text-slate-500 mt-4 leading-relaxed">We combine cutting-edge printing technology
                         with expert craftsmanship to deliver products that exceed expectations.</p>
                     <a href="{{ route('flow-pc.find-store') }}"
                         class="inline-flex items-center gap-2 mt-6 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                         Find a store near you <i data-lucide="arrow-right" class="w-4 h-4"></i>
                     </a>
                 </div>
                 <div class="col-span-12 lg:col-span-7">
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                         @php
                             $features = [
                                 [
                                     'icon' => 'award',
                                     'color' => 'brand',
                                     'title' => 'Premium Quality',
                                     'desc' =>
                                         'Museum-grade materials and professional-grade printers for stunning results.',
                                 ],
                                 [
                                     'icon' => 'zap',
                                     'color' => 'amber',
                                     'title' => 'Fast Turnaround',
                                     'desc' =>
                                         'Most orders ready within 48 hours. Same-day pickup available at select stores.',
                                 ],
                                 [
                                     'icon' => 'palette',
                                     'color' => 'violet',
                                     'title' => 'Design Studio',
                                     'desc' => 'Powerful yet intuitive editor with templates, text tools, and filters.',
                                 ],
                                 [
                                     'icon' => 'shield-check',
                                     'color' => 'emerald',
                                     'title' => 'Satisfaction Guaranteed',
                                     'desc' => 'Not happy? We\'ll reprint or refund your order. No questions asked.',
                                 ],
                             ];
                         @endphp

                         @foreach ($features as $feature)
                             <div class="feature-card bg-white border border-slate-200 rounded-2xl p-6">
                                 <div
                                     class="w-11 h-11 bg-{{ $feature['color'] }}-50 rounded-xl flex items-center justify-center mb-4">
                                     <i data-lucide="{{ $feature['icon'] }}"
                                         class="w-5 h-5 text-{{ $feature['color'] }}-600"></i>
                                 </div>
                                 <h4 class="font-bold text-slate-900 mb-1.5">{{ $feature['title'] }}</h4>
                                 <p class="text-sm text-slate-500 leading-relaxed">{{ $feature['desc'] }}</p>
                             </div>
                         @endforeach
                     </div>
                 </div>
             </div>
         </section>

         {{-- ===== SECTION 5: CUSTOMER REVIEWS ===== --}}
         <section class="max-w-[1400px] mx-auto py-16 border-t border-slate-100">
             <div class="flex items-end justify-between mb-10">
                 <div>
                     <h2 class="text-3xl xl:text-4xl font-extrabold text-slate-900 tracking-tight">What Our Customers Say
                     </h2>
                     <p class="text-lg text-slate-500 mt-2">Trusted by thousands of happy customers worldwide.</p>
                 </div>
                 <a href="#"
                     class="hidden lg:flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                     Read all reviews <i data-lucide="arrow-right" class="w-4 h-4"></i>
                 </a>
             </div>

             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 @php
                     $reviews = [
                         [
                             'name' => 'Sarah Mitchell',
                             'initials' => 'SM',
                             'color' => 'brand',
                             'text' =>
                                 'The canvas print quality blew me away. Colors are vibrant and true to the original photo. Arrived in perfect condition within two days. Already ordered three more for the living room!',
                             'label' => 'Verified buyer',
                         ],
                         [
                             'name' => 'James Thornton',
                             'initials' => 'JT',
                             'color' => 'emerald',
                             'text' =>
                                 'Created a 100-page photo book of our wedding and it turned out absolutely stunning. The paper quality is fantastic and the binding is solid. Best decision for preserving our memories.',
                             'label' => 'Verified buyer',
                         ],
                         [
                             'name' => 'Amara Osei',
                             'initials' => 'AO',
                             'color' => 'rose',
                             'text' =>
                                 'The design studio is incredibly intuitive. I uploaded my artwork and added custom mugs designed under five minutes. The print quality on the mugs exceeded my expectations. Will definitely be back.',
                             'label' => 'Verified buyer',
                         ],
                     ];
                 @endphp

                 @foreach ($reviews as $review)
                     <div class="review-card-home bg-white border border-slate-200 rounded-2xl p-7">
                         <div class="flex items-center gap-1 mb-4">
                             <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                             <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                             <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                             <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                             <i data-lucide="star" class="w-5 h-5 text-amber-400 fill-amber-400"></i>
                         </div>
                         <p class="text-slate-600 leading-relaxed text-[15px]">{{ $review['text'] }}</p>
                         <div class="mt-6 pt-5 border-t border-slate-100 flex items-center gap-3">
                             <div
                                 class="w-10 h-10 bg-{{ $review['color'] }}-100 text-{{ $review['color'] }}-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                                 {{ $review['initials'] }}
                             </div>
                             <div>
                                 <p class="text-sm font-semibold text-slate-900">{{ $review['name'] }}</p>
                                 <p class="text-xs text-emerald-600 font-medium">{{ $review['label'] }}</p>
                             </div>
                         </div>
                     </div>
                 @endforeach
             </div>
         </section>

         {{-- ===== SECTION 6: CTA BANNER ===== --}}
         <section class="max-w-[1400px] mx-auto pt-8 pb-4">
             <div
                 class="bg-gradient-to-r from-brand-600 via-violet-600 to-brand-500 rounded-3xl px-12 py-14 text-center relative overflow-hidden">
                 <div class="absolute inset-0 opacity-10"
                     style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;">
                 </div>
                 <div class="relative">
                     <h2 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight">Ready to create something
                         beautiful?</h2>
                     <p class="text-lg text-brand-100 mt-3 max-w-xl mx-auto">Start designing your custom prints today with
                         our easy-to-use design studio.</p>
                     <div class="flex items-center justify-center gap-4 mt-8">
                         <a href="#products"
                             class="bg-white hover:bg-slate-50 text-brand-700 font-semibold px-8 py-3.5 rounded-xl transition-all duration-200 active:scale-95 flex items-center gap-2 shadow-lg">
                             <i data-lucide="pen-tool" class="w-4 h-4"></i>
                             Start Designing
                         </a>
                         <a href="{{ route('flow-pc.find-store') }}"
                             class="bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-3.5 rounded-xl border border-white/20 transition-all duration-200 active:scale-95 flex items-center gap-2">
                             <i data-lucide="map-pin" class="w-4 h-4"></i>
                             Find a Store
                         </a>
                     </div>
                 </div>
             </div>
         </section>

     </div>
 @endsection
